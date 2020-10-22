<?php

namespace app\plugins\exchange\handlers;

use app\handlers\HandlerBase;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\exchange\forms\exchange\core\Rollback;
use app\plugins\exchange\Plugin;

class OrderRefundConfirmedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            if ($event->order_refund->type == 1 && $event->order_refund->status == 2) {
                $order = Order::findOne($event->order_refund->order_id);
                if (!$order) {
                    throw new \Exception('订单不存在');
                }
                if ($order->sign != (new Plugin())->getName()) {
                    return true;
                };
                //////////////兑换订单专用//////////////////////////////////////
                ///
                $sign = current($order->detail)['goods']['sign'];
                if ($sign === 'exchange') {
                    return true;
                }
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    (new Rollback())->goods($order);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \Yii::error($e);
                    throw $e;
                }
            }
        });
    }
}
