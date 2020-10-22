<?php

namespace app\plugins\step\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\models\StepOrder;
use app\plugins\step\models\StepUser;
use app\plugins\step\Plugin;

class OrderCanceledHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            /** @var OrderEvent $event */
            // 步数宝商品退款
            if ($event->order->sign !== (new Plugin())->getName()){
                return true;
            }
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $stepOrder = StepOrder::findOne([
                    'token' => $event->order->token,
                    'mall_id' => $event->order->mall_id,
                    'order_id' => $event->order->id,
                ]);

                if (!$stepOrder) {
                    throw new \Exception('步数宝订单不存在,id=>' . $event->order->id);
                }

                $stepUser = StepUser::findOne([
                    'mall_id' => $event->order->mall_id,
                    'user_id' => $event->order->user_id,
                    'is_delete' => 0
                ]);
                if (!$stepUser) {
                    throw new \Exception('用户不存在');
                }

                (new CommonCurrencyModel())->setUser($stepUser)->add(floor($stepOrder->currency), '商品取消', '订单为:'.$event->order->order_no);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        });
    }
}
