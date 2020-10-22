<?php

namespace app\plugins\exchange\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\plugins\exchange\forms\exchange\core\Rollback;
use app\plugins\exchange\Plugin;

class OrderCanceledHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            /** @var OrderEvent $event */
            if ($event->order->sign !== (new Plugin())->getName()) {
                return true;
            }
            $sign = current($event->order->detail)['goods']['sign'];
            if ($sign === 'exchange') {
                \Yii::error('兑换订单');
                return true;
            }
            //////////////兑换订单专用//////////////////////////////////////
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                (new Rollback())->goods($event->order);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e);
                throw $e;
            }
        });
    }
}
