<?php

namespace app\plugins\step\handlers;

use app\models\Order;
use app\models\OrderRefund;
use app\handlers\HandlerBase;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\models\StepOrder;
use app\plugins\step\models\StepUser;
use app\plugins\step\Plugin;

class OrderRefundConfirmedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            if ($event->order_refund->type == 1 && $event->order_refund->status == 2) {
                // 步数宝商品退款
                $order = Order::findOne($event->order_refund->order_id);
                if (!$order) {
                    throw new \Exception('订单不存在');
                }
                if ($order->sign != (new Plugin())->getName()) {
                    return true;
                };
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $stepOrder = StepOrder::findOne([
                        'order_id' => $order->id,
                        'mall_id' => $order->mall_id
                    ]);
                    if (!$stepOrder) {
                        throw new \Exception('步数宝订单不存在,id=>' . $event->order->id);
                    }

                    $stepUser = StepUser::findOne([
                        'mall_id' => $order->mall_id,
                        'user_id' => $order->user_id,
                        'is_delete' => 0
                    ]);

                    if (!$stepUser) {
                        throw new \Exception('用户不存在');
                    }

                    (new CommonCurrencyModel())->setUser($stepUser)->add(floor($stepOrder->currency), '商品退款', '订单为:' . $order->order_no);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        });
    }
}
