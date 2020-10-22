<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 17:37
 */

namespace app\plugins\bonus\jobs;

use app\models\Mall;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;
use app\plugins\bonus\events\CaptainEvent;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\forms\common\CommonCaptainLog;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainLog;
use app\plugins\bonus\models\BonusCaptainRelation;
use app\plugins\bonus\models\BonusSetting;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class BecomeCaptainJob extends BaseObject implements JobInterface
{
    public $user_id;
    public $mall;

    /** @var User */
    public $user;

    /**当前队长所有下级信息**/
    public $temps = [];

    /**@var BonusCaptain $captain * */
    public $captain;

    /**记录查询过的分销商id,避免死链**/
    public $exists = [];

    /**1为正常审核  2为其他方式调用**/
    public $flag = 1;

    public function execute($queue)
    {
        \Yii::$app->setMall(Mall::findOne($this->mall->id));
        $user = UserIdentity::find()->where([
            'user_id' => $this->user_id,
        ])->select('is_distributor')->one();
        $this->user = $user;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->flag != 1) {
                $this->deleteRelation();
            }
            $this->handle($this->user_id);
            $this->end();
            $transaction->commit();
            $this->log($this->flag);
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->captain->status = CommonCaptain::STATUS_APPLYING;
            $this->captain->save();
            \Yii::error("成为队长:" . $e->getMessage());
            CommonCaptainLog::create("成为队长".BonusCaptainLog::BONUS_EXCEPTION,0,[$e]);
            throw new \Exception($e->getMessage());
        };
    }

    public function handle($user_id)
    {
        $this->exists[] = $user_id;
        $subQuery = BonusCaptain::find()->select('user_id')->where(['mall_id' => \Yii::$app->mall->id, 'status' => CommonCaptain::STATUS_BECOME,'is_delete'=>0]);
        $query = UserInfo::find()->alias('u')->where(['u.parent_id' => $user_id,])->with(['identity'])
            ->andWhere(['not in', 'user_id', $subQuery]);
        /**
         * @var User $user
         */
        foreach ($query->batch(1000) as $users) {
            foreach ($users as $user) {
                if (!in_array($user->user_id, $this->exists)) {
                    $temp['captain_id'] = $this->user_id;
                    $temp['user_id'] = $user->user_id;
                    $data[] = $temp;
                    if ($user->identity->is_distributor == 1) {
                        $this->handle($user->user_id);
                    }
                }
            }
            if (!empty($data) && count($data) > 500) {
                $chunk = array_chunk($data, 500);
                foreach ($chunk as $item) {
                    foreach ($item as $k => $v) {
                        if (in_array($v, $this->temps)) {
                            unset($item[$k]);
                        }
                    }
                    \Yii::$app->db->createCommand()->batchInsert(BonusCaptainRelation::tableName(), ['captain_id', 'user_id'], $item)->execute();
                }
                $this->temps = array_merge($this->temps, $data);
            } elseif (!empty($data)) {
                foreach ($data as $k => $v) {
                    if (in_array($v, $this->temps)) {
                        unset($data[$k]);
                    }
                }
                \Yii::$app->db->createCommand()->batchInsert(BonusCaptainRelation::tableName(), ['captain_id', 'user_id'], $data)->execute();
                $this->temps = array_merge($this->temps, $data);
            }
            unset($data);
        }
    }

    /**
     * 处理完关联关系
     */
    public function end()
    {
        //查询上级链中是否已经有队长
        $relation = BonusCaptainRelation::findOne(['user_id' => $this->user_id, 'is_delete' => 0]);
        if ($relation) {
            //需要删除上级队长中当前队长的成员
            $parentCaptain = BonusCaptain::findOne(['user_id' => $relation->captain_id, 'status' => 1, 'is_delete' => 0, 'mall_id' => $this->mall->id]);
            if ($parentCaptain) {
                if (!empty($this->temps)) {
                    $ids = array_column($this->temps, 'user_id');
                    //还需要删除自己和上级队长的联系
                    $ids = array_merge($ids, [$this->user_id]);
                    BonusCaptainRelation::deleteAll(['user_id' => $ids, 'captain_id' => $relation->captain_id]);
                } else {
                    $relation->delete();
                }
                $parentCaptain->all_member = BonusCaptainRelation::find()->where(['captain_id' => $relation->captain_id, 'is_delete' => 0])->groupBy(['user_id'])->count();
                $parentCaptain->save();
            }
        }
        $this->captain->status = CommonCaptain::STATUS_BECOME;
        $this->captain->apply_at = mysql_timestamp();
        $this->captain->all_member = BonusCaptainRelation::find()->where(['captain_id' => $this->user_id, 'is_delete' => 0])->groupBy(['user_id'])->count();
        $this->captain->save();

        $parentId = !empty($parentCaptain) && !empty($relation->captain_id) ? $relation->captain_id : 0;
        if ($this->flag == 1) {
            \Yii::$app->trigger(BonusCaptain::EVENT_BECOME, new CaptainEvent([
                'captain' => $this->captain,
                'parentId' => $parentId
            ]));
        }
    }

    public function deleteRelation()
    {
        BonusCaptainRelation::deleteAll(['captain_id' => $this->captain->user_id]);
    }

    private function log($flag)
    {
        if ($flag == 1) {
            $temp = '正常审核';
        } else {
            $temp = '其他方式调用';
        }
        try {
            if (!empty($this->temps)) {
                $log = array_column($this->temps, 'user_id');
            } else {
                $log = [];
            }
            CommonCaptainLog::create($temp.BonusCaptainLog::BECOME_CAPTAIN, $this->user_id, $log);
        } catch (\Exception $exception) {
            \Yii::error('记录队长日志出错');
            \Yii::error($exception);
        }
    }
}