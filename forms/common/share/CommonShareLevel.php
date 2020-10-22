<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/10/19
 * Time: 14:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\share;


use app\models\Mall;
use app\models\Model;
use app\models\Share;
use app\models\ShareCash;
use app\models\ShareLevel;
use app\models\User;

/**
 * Class CommonShareLevel
 * @package app\forms\common\share
 * @property Mall $mall
 * @property User $user
 * @property Share $share
 */
class CommonShareLevel extends Model
{
    private static $instance;
    public $mall;
    public $user;
    public $userId;
    public $share;

    const CHILDREN_COUNT = 1; // 下线用户数
    const TOTAL_MONEY = 2; // 累计佣金
    const TOTAL_CASH = 3; // 已提现佣金

    public static function getInstance($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    public function getOptions()
    {
        $list = ShareLevel::find()->select('level')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->column();

        $newList = [];
        for ($i = 1; $i <= 10; $i++) {
            $newList[] = [
                'name' => '等级' . $i,
                'level' => $i,
                'disabled' => in_array($i, $list),
            ];
        }

        return $newList;
    }

    /**
     * @param $id
     * @return ShareLevel|null
     */
    public function getDetail($id)
    {
        if (!$id) {
            return null;
        }
        /* @var ShareLevel $shareLevel*/
        $shareLevel = ShareLevel::find()->where([
            'id' => $id,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->one();
        if ($shareLevel && $shareLevel->condition_type == 1) {
            $shareLevel->condition = intval($shareLevel->condition);
        }
        return $shareLevel;
    }

    public function destroy($id)
    {
        $shareLevel = $this->getDetail($id);
        if (!$shareLevel) {
            throw new \Exception('所选择的分销商等级不存在或已删除，请刷新后重试');
        }
        $shareExists = Share::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1, 'level' => $shareLevel->level
        ])->exists();
        if ($shareExists) {
            throw new \Exception('该分销商等级下还有分销商存在，暂时不能删除');
        }
        $shareLevel->is_delete = 1;
        if (!$shareLevel->save()) {
            throw new \Exception($this->getErrorMsg($shareLevel));
        }
        return true;
    }

    public function switchStatus($id)
    {
        $shareLevel = $this->getDetail($id);
        if (!$shareLevel) {
            throw new \Exception('所选择的分销商等级不存在或已删除，请刷新后重试');
        }
        $shareExists = Share::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1, 'level' => $shareLevel->level
        ])->exists();
        if ($shareExists) {
            throw new \Exception('该分销商等级下还有分销商存在，暂时不能关闭');
        }
        $shareLevel->status = $shareLevel->status ? 0 : 1;
        if (!$shareLevel->save()) {
            throw new \Exception($this->getErrorMsg($shareLevel));
        }
        return true;
    }

    /**
     * @param integer $conditionType 升级方式1--下线用户数|2--累计佣金|3--已提现佣金
     * @return bool
     * @throws \Exception
     * 分销商升级等级
     */
    public function levelShare($conditionType)
    {
        $share = $this->getShare();
        if (!$share) {
            throw new \Exception('分销商不存在');
        }
        $condition = $this->getCondition($conditionType, $share);
        \Yii::error($condition);
        /* @var ShareLevel $shareLevel */
        $shareLevel = ShareLevel::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1, 'condition_type' => $conditionType,
            'is_auto_level' => 1
        ])->andWhere(['<=', 'condition', $condition])
            ->andWhere(['>', 'level', $share->level])
            ->orderBy(['level' => SORT_DESC])
            ->one();
        if (!$shareLevel) {
            \Yii::error('没有更高分销等级可升级');
            return false;
        }
        return $this->changeLevel($shareLevel->level);
    }

    /**
     * @param $level
     * @return bool
     * @throws \Exception
     */
    public function changeLevel($level)
    {
        $share = $this->getShare();
        if (!$share) {
            throw new \Exception('分销商不存在');
        }
        $share->level = $level;
        $share->level_at = mysql_timestamp();
        if (!$share->save()) {
            \Yii::error('升级分销商等级出错');
            \Yii::error($this->getErrorMsg($share));
            return false;
        }
        return true;
    }

    /**
     * @return Share|null
     * @throws \Exception
     * 获取分销商
     */
    private function getShare()
    {
        if ($this->share) {
            return $this->share;
        }
        $share = null;
        if ($this->user) {
            $share = $this->user->share;
        } elseif ($this->userId) {
            $share = Share::find()->with('firstChildren')->where([
                'user_id' => $this->userId, 'is_delete' => 0, 'mall_id' => $this->mall->id, 'status' => 1
            ])->one();
        }
        if (!$share) {
            throw new \Exception('不存在分销商');
        }
        $this->share = $share;
        return $share;
    }

    /**
     * @param int $conditionType
     * @param Share $share
     * @return float
     * 获取升级条件
     */
    private function getCondition($conditionType, $share)
    {
        $condition = 0;
        switch ($conditionType) {
            case self::CHILDREN_COUNT:
                $condition = $share->all_children;
                break;
            case self::TOTAL_MONEY:
                $condition = $share->total_money;
                break;
            case self::TOTAL_CASH:
                $condition = ShareCash::find()->where([
                    'mall_id' => $this->mall->id, 'user_id' => $share->user_id, 'is_delete' => 0, 'status' => 2,
                ])->select('SUM(price)')->scalar();
                break;
            default:
                break;
        }
        return $condition;
    }

    protected $shareLevelList;

    /**
     * @param $level
     * @return ShareLevel|null
     * 通过分销商等级来获取分销等级
     */
    public function getShareLevelByLevel($level)
    {
        if (!$level) {
            return null;
        }
        if (isset($this->shareLevelList[$level]) && $this->shareLevelList[$level]) {
            return $this->shareLevelList[$level];
        }
        /* @var ShareLevel $shareLevel*/
        $shareLevel = ShareLevel::find()->where([
            'level' => $level,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->one();
        if ($shareLevel && $shareLevel->condition_type == 1) {
            $shareLevel->condition = intval($shareLevel->condition);
        }
        $this->shareLevelList[$level] = $shareLevel;
        return $shareLevel;
    }

    public function levelUp()
    {
        $share = $this->getShare();
        if (!$share) {
            throw new \Exception('用户不是分销商，无法升级分销商等级');
        }
        /* @var ShareLevel $temp */
        /* @var ShareLevel $shareLevel */
        $shareLevel = null;
        $temp = null;
        for ($i = 1; $i <= 3; $i++) {
            $condition = $this->getCondition($i, $share);
            $temp = ShareLevel::find()->where([
                'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1, 'condition_type' => $i,
                'is_auto_level' => 1
            ])->andWhere(['<=', 'condition', $condition])
                ->andWhere(['>', 'level', $share->level])
                ->orderBy(['level' => SORT_DESC])
                ->one();
            if ($temp && (!$shareLevel || ($shareLevel->level < $temp->level))) {
                $shareLevel = $temp;
            }
        }
        if (!$shareLevel) {
            return [
                'status' => 0,
                'level_name' => '升级失败',
                'condition_text' => '未满足升级条件'
            ];
        } else {
            $this->changeLevel($shareLevel->level);
            $type = [
                1 => '下线用户数',
                2 => '累计佣金',
                3 => '已提现佣金',
            ];
            $condition = [
                1 => '人',
                2 => '元',
                3 => '元',
            ];
            return [
                'status' => 1,
                'level_name' => '升级到' . $shareLevel->name,
                'condition_text' => $type[$shareLevel->condition_type] . '达到' . round($shareLevel->condition, 2) . $condition[$shareLevel->condition_type],
            ];
        }
    }

    public function getList()
    {
        $shareLevelList = ShareLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1,
        ])->select(['id', 'level', 'name'])->orderBy(['level' => SORT_ASC])->all();
        array_unshift($shareLevelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);
        return $shareLevelList;
    }
}
