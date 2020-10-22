<?php


namespace app\plugins\community\handlers;

use app\events\OrderEvent;
use app\models\Order;
use app\plugins\community\models\CommunityOrder;
use yii\db\Exception;
use app\handlers\HandlerBase;


class OrderCanceledHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            /** @var OrderEvent $event */
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('community', $permission)) {
                \Yii::error('社区团购插件不存在');
                return;
            }
            if ($event->order->sign != 'community') {
                \Yii::error('非社区团购订单');
                return;
            }
            \Yii::error('社区团购订单取消回调开始：');
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
//                CommunityOrder::updateAll(['is_delete' => 1], ['order_id' => $event->order->id]);todo 不减销量，提高虚拟销量
                $t->commit();
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('社区团购订单取消事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
