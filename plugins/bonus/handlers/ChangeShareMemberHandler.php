<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/5
 * Time: 9:25
 */

namespace app\plugins\bonus\handlers;

use app\events\ShareMemberEvent;
use app\handlers\HandlerBase;
use app\models\Share;
use app\plugins\bonus\events\MemberEvent;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\forms\common\CommonCaptainLog;
use app\plugins\bonus\forms\common\CommonForm;
use app\plugins\bonus\jobs\BecomeCaptainJob;
use app\plugins\bonus\jobs\RemoveCaptainJob;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCaptainLog;
use app\plugins\bonus\models\BonusCaptainRelation;
use app\plugins\bonus\models\BonusMembers;

class ChangeShareMemberHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(\app\handlers\HandlerRegister::CHANGE_SHARE_MEMBER, function ($event) {
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('bonus', $permission)) {
                return;
            }
            /* @var ShareMemberEvent $event */
            $t = \Yii::$app->db->beginTransaction();
            try {
                $share = Share::findOne(['user_id' => $event->userId, 'status' => 1]);
                if ($share) {
                    if (
                        $share->is_delete == 1
                        && (time() >= strtotime($share->deleted_at) - 30
                            && time() <= strtotime($share->deleted_at) + 30)
                    ) {
                        //删除时间在当前时间1分钟内视为删除分销商
                        \Yii::error("删除分销商事件：" . $event->parentId);
                        $this->removeShare($event);
                    } elseif ($share->is_delete == 1) {
                        $this->changeCommonMember($event);
                    } else {
                        $this->changeShare($event);
                    }
                } else {
                    if ($event->parentId == 0 && $event->beforeParentId != 0) {
                        $this->removeCommonMember($event);
                    } else {
                        $this->changeCommonMember($event);
                    }
                }
                $t->commit();
                $log = [
                    'parentId' => $event->parentId,
                    'userId' => $event->userId,
                    'beforeParentId' => $event->beforeParentId,
                ];
                CommonCaptainLog::create(BonusCaptainLog::CHANGE_PARENT, $event->userId, $log);
            } catch (\Exception $e) {
                $t->rollBack();
                \Yii::error('更改上级对队长的影响：');
                \Yii::error($e);
                CommonCaptainLog::create("更改上级对队长的影响" . BonusCaptainLog::BONUS_EXCEPTION, 0, [$e]);
                throw $e;
            }

            return true;
        });
    }

    private function changeShare($event)
    {
        /**@var ShareMemberEvent $event * */
        CommonForm::$exists = [];
        $user_id = CommonForm::findFirstCaptain($event->userId);
        \Yii::error('user_id:=====' . $user_id);
        $captain = BonusCaptain::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'status' => CommonCaptain::STATUS_BECOME,
            'is_delete' => 0,
            'user_id' => $event->userId
        ]);
        $pcaptain = BonusCaptain::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'status' => CommonCaptain::STATUS_BECOME,
            'is_delete' => 0,
            'user_id' => $event->beforeParentId
        ]);
        if ($user_id && empty($captain)) {
            //变更后的上级分销商存在队长，且触发事件的用户不是队长
            $p = BonusCaptain::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'status' => CommonCaptain::STATUS_BECOME,
                'is_delete' => 0,
                'user_id' => $user_id
            ]);
            $dataArr = [
                'mall' => \Yii::$app->mall,
                'user_id' => $user_id,
                'captain' => $p,
                'flag' => 2
            ];
            $class = new BecomeCaptainJob($dataArr);
            \Yii::$app->queue->delay(0)->push($class);
        }

        if ($pcaptain && empty($captain)) {
            //变更前的上级分销商存在队长，且触发事件的用户不是队长
            $dataArr = [
                'mall' => \Yii::$app->mall,
                'user_id' => $event->beforeParentId,
                'captain' => $pcaptain,
                'flag' => 2
            ];
            $class = new BecomeCaptainJob($dataArr);
            \Yii::$app->queue->delay(0)->push($class);
        }
    }

    private function removeShare($event)
    {
        /**@var ShareMemberEvent $event * */
        //移除分销商
        $captain = BonusCaptain::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => CommonCaptain::STATUS_BECOME,
            'is_delete' => 0,
            'user_id' => $event->userId
        ])->one();
        //移除的分销商正好是队长
        if (!empty($captain)) {
            $dataArr = [
                'mall' => \Yii::$app->mall,
                'user_id' => $event->userId,
                'captain' => $captain
            ];
            $class = new RemoveCaptainJob($dataArr);
            \Yii::$app->queue->delay(0)->push($class);
        } else {
            if (empty($event->parentId)) {
                //此时已经解除了关联
                $lastParent = BonusCaptain::findOne([
                    'user_id' => $event->beforeParentId,
                    'status' => CommonCaptain::STATUS_BECOME,
                    'is_delete' => 0
                ]);
                if ($lastParent) {
                    $user_id = $event->beforeParentId;
                    $captain = $lastParent;
                } else {
                    CommonForm::$exists = [];
                    $user_id = CommonForm::findFirstCaptain($event->beforeParentId);
                    $captain = BonusCaptain::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'status' => CommonCaptain::STATUS_BECOME,
                        'is_delete' => 0,
                        'user_id' => $user_id
                    ]);
                }
                if ($captain) {
                    $dataArr = [
                        'mall' => \Yii::$app->mall,
                        'user_id' => $user_id,
                        'captain' => $captain,
                        'flag' => 2
                    ];
                    $class = new BecomeCaptainJob($dataArr);
                    \Yii::$app->queue->delay(0)->push($class);
                }
            }
        }
    }

    private function changeCommonMember($event)
    {
        /**@var ShareMemberEvent $event * */
        $captain = BonusCaptain::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => $event->parentId,
            'is_delete' => 0,
            'status' => 1
        ]);
        if (empty($captain)) {
            $p = CommonForm::findFirstCaptain($event->parentId);
            if (!empty($p)) {
                $captain = BonusCaptain::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => $p,
                    'is_delete' => 0,
                    'status' => 1
                ]);
            }
        }
        if ($captain) {
            $relation = BonusCaptainRelation::findOne(['captain_id' => $captain->user_id, 'user_id' => $event->userId]);
            if (empty($relation)) {
                $r = new BonusCaptainRelation();
                $r->captain_id = $captain->user_id;
                $r->user_id = $event->userId;
                $r->save();
            }
            $captain->all_member = BonusCaptainRelation::find()->where([
                'captain_id' => $captain->user_id,
                'is_delete' => 0
            ])->count();
            $captain->save();

            //成为下级成员触发队长升级事件
            $e = new MemberEvent([
                'captain' => $captain
            ]);
            \Yii::$app->trigger(BonusMembers::UPDATE_LEVEL, $e);
        }

        $this->removeCommonMember($event);
    }

    private function removeCommonMember($event)
    {
        if ($event->beforeParentId == $event->parentId) {
            return;
        }
        /**@var ShareMemberEvent $event * */
        $captain = BonusCaptain::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => $event->beforeParentId,
            'is_delete' => 0,
            'status' => 1
        ]);
        if (!empty($captain)) {
            // 如果上级链中有队长
            $relation = BonusCaptainRelation::findOne(['captain_id' => $captain->user_id, 'user_id' => $event->userId]);
            if (!empty($relation)) {
                //理论上一定会执行这里
                $relation->delete();
                $post = BonusCaptain::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => $captain->user_id,
                    'status' => CommonCaptain::STATUS_BECOME
                ]);
                $post->all_member = BonusCaptainRelation::find()->where([
                    'captain_id' => $captain->user_id,
                    'is_delete' => 0
                ])->count();
                $post->save();
            }
        }
    }
}
