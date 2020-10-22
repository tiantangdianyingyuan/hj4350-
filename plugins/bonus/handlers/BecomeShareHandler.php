<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/5
 * Time: 9:25
 */

namespace app\plugins\bonus\handlers;

use app\events\ShareEvent;
use app\handlers\HandlerBase;
use app\plugins\bonus\forms\common\CommonCaptainLog;
use app\plugins\bonus\forms\common\CommonForm;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainLog;
use app\plugins\bonus\models\BonusCaptainRelation;

class BecomeShareHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(\app\handlers\HandlerRegister::BECOME_SHARE, function ($event) {
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('bonus', $permission)) {
                return;
            }
            try {
                /* @var ShareEvent $event */
                if ($event->share->status == 1) {
                    CommonForm::$exists = [];
                    $user_id = CommonForm::findFirstCaptain($event->share->user_id);
                    if (!empty($user_id)) {
                        $relation = BonusCaptainRelation::findOne(['captain_id'=>$user_id,'user_id'=>$event->share->user_id]);
                        if (empty($relation)) {
                            $t = \Yii::$app->db->beginTransaction();
                            $relation = new BonusCaptainRelation();
                            $relation->captain_id = $user_id;
                            $relation->user_id = $event->share->user_id;
                            $relation->save();

                            $post = BonusCaptain::findOne(['user_id'=>$user_id]);
                            $post->all_member = BonusCaptainRelation::find()->where(['captain_id'=>$user_id,'is_delete'=>0])->count();
                            $post->save();
                            $t->commit();

                            $log = [
                                'user_id'=>$user_id,
                                'share' => $event->share->user_id,
                            ];
                            CommonCaptainLog::create(BonusCaptainLog::BECOME_SHARE_AFFECT,$user_id,$log);
                        }
                    }
                }
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('成为分销商对队长的影响：');
                \Yii::error($exception);
                CommonCaptainLog::create(BonusCaptainLog::BONUS_EXCEPTION, 0, [$exception]);
                throw $exception;
            }
            return true;
        });
    }
}