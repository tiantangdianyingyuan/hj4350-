<?php


namespace app\plugins\gift\handlers;

use app\models\Order;
use app\plugins\gift\events\OrderEvent;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftUserOrder;
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
            if (!in_array('gift', $permission)) {
                \Yii::error('礼物插件不存在');
                return;
            }
            if ($event->order->sign != 'gift') {
                \Yii::error('非礼物订单');
                return;
            }
            \Yii::error('礼物订单取消回调开始：');
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                $gift_order = GiftOrder::findOne(['order_id' => $event->order->id]);
                if (empty($gift_order)) {
                    \Yii::error('礼物领取订单不存在order_id:' . $event->order->id);
                    $t->rollBack();
                    return;
                }
                $gift_order->order_id = 0;
//                $gift_order->buy_order_detail_id = 0;
                if (!$gift_order->save()) {
                    throw new \Exception($gift_order->errors[0]);
                }
                $user_order = GiftUserOrder::findOne($gift_order->user_order_id);
                if (empty($gift_order)) {
                    throw new \Exception('礼物领取记录不存在user_order_id:' . $gift_order->user_order_id);
                }
                $user_order->is_receive = 0;
                if (!$user_order->save()) {
                    throw new \Exception($user_order->errors[0]);
                }

                $t->commit();
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('礼物订单取消事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
