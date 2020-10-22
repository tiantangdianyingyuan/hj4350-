<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Jack_guo
 * Date: 2019/7/5
 * Time: 9:25
 */

namespace app\plugins\region\handlers;

use app\events\ShareMemberEvent;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Share;
use app\plugins\region\models\RegionUser;
use app\plugins\region\models\RegionUserInfo;

class ChangeShareMemberHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(
            \app\handlers\HandlerRegister::CHANGE_SHARE_MEMBER,
            function ($event) {
                //权限判断
                $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
                if (!in_array('region', $permission)) {
                    return;
                }
                /* @var ShareMemberEvent $event */
                $t = \Yii::$app->db->beginTransaction();
                try {
                    $share = Share::findOne(['user_id' => $event->userId, 'status' => 1]);
                    if ($share) {
                        \Yii::error("删除分销商同步删除区域事件：" . $share->user_id);
                        if ($share->is_delete == 1) {
                            //同步解除区域
                            $region = RegionUser::findOne(
                                [
                                    'user_id' => $share->user_id,
                                    'is_delete' => 0,
                                    'mall_id' => \Yii::$app->mall->id,
                                    'status' => 1
                                ]
                            );
                            if ($region) {
                                $user_info = RegionUserInfo::findOne(['user_id' => $share->user_id]);
                                $user_info->reason = '解除分销商同步解除区域身份';
                                if (!$user_info->save()) {
                                    $t->rollBack();
                                    throw new \Exception((new Model())->getErrorMsg($user_info->errors));
                                }
                                $region->status = -1;
                                $region->applyed_at = mysql_timestamp();
                                if (!$region->save()) {
                                    $t->rollBack();
                                    throw new \Exception((new Model())->getErrorMsg($region->errors));
                                }
                            }
                        }
                    }
                    $t->commit();
                } catch (\Exception $e) {
                    $t->rollBack();
                    \Yii::error('删除分销商同步删除区域错误：');
                    \Yii::error($e);
                    throw $e;
                }
            }
        );
    }
}
