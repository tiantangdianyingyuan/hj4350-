<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/10
 * Time: 15:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\models\Mall;
use app\models\Share;
use app\models\ShareSetting;
use app\models\User;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property User $user
 * @property Mall $mall
 */
class ChangeParentJob extends BaseObject implements JobInterface
{
    public $mall;
    public $user;
    public $user_id;
    public $beforeParentId;// 变更前的上级id
    public $parentId; // 变更后的上级id
    private $level;

    public function execute($queue)
    {
        if ($this->beforeParentId == $this->parentId) {
            return true;
        }
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        $this->user = User::find()->where(['id' => $this->user_id])->with('share')->one();
        $this->level = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
        \Yii::error('--上级更改--');
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->change($this->parentId, 1);
            $this->change($this->beforeParentId, 1);
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('用户变更上级出错');
            \Yii::error($exception);
        }
    }

    /**
     * @param $parentId
     * @param int $root
     * @return bool|mixed
     * @throws \Exception
     * 修改分销商的直属下级数量和总下级数量
     */
    private function change($parentId, $root = 3)
    {
        if ($root > $this->level) {
            return true;
        }

        if ($parentId == 0) {
            return true;
        }

        /* @var Share $parent */
        $parent = Share::find()->with(['userInfo', 'thirdChildren'])
            ->where(['user_id' => $parentId, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();
        if (!$parent) {
            throw new \Exception('错误的上级id');
        }

        $parent->first_children = count($parent->firstChildren);
        $parent->all_children = count($parent->firstChildren);
        if ($this->level > 1) {
            $parent->all_children += count($parent->secondChildren);
            if ($this->level > 2) {
                $parent->all_children += count($parent->thirdChildren);
            }
        }

        if (!$parent->save()) {
            throw new \Exception('第' . $root . '层出错');
        }

        $root++;
        return $this->change($parent->userInfo->parent_id, $root);
    }
}
