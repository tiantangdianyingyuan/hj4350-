<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Jack_guo
 * Date: 2019/7/5
 * Time: 9:25
 */

namespace app\plugins\stock\handlers;

use app\events\ShareMemberEvent;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Share;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;

class ChangeShareMemberHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(\app\handlers\HandlerRegister::CHANGE_SHARE_MEMBER, function ($event) {
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('stock', $permission)) {
                return;
            }
            /* @var ShareMemberEvent $event */
            $t = \Yii::$app->db->beginTransaction();
            try {
                $share = Share::findOne(['user_id' => $event->userId, 'status' => 1]);
                if ($share) {
                    if ($share->is_delete == 1) {
                        \Yii::error("删除分销商同步删除股东事件：" . $share->user_id);
                        //同步解除股东
                        $stock = StockUser::findOne(['user_id' => $share->user_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'status' => 1]);
                        if ($stock) {
                            $user_info = StockUserInfo::findOne(['user_id' => $share->user_id]);
                            $user_info->reason = '解除分销商同步解除股东身份';
                            if (!$user_info->save()) {
                                throw new \Exception((new Model())->getErrorMsg($user_info));
                            }
                            $stock->status = -1;
                            $stock->applyed_at = mysql_timestamp();
                            if (!$stock->save()) {
                                throw new \Exception((new Model())->getErrorMsg($stock));
                            }
                        }
                    }
                }
                $t->commit();
            } catch (\Exception $e) {
                $t->rollBack();
                \Yii::error('删除分销商同步删除股东错误：');
                \Yii::error($e);
                throw $e;
            }
        });
    }
}