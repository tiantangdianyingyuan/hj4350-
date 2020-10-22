<?php

namespace app\plugins\step\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\models\StepOrder;
use app\plugins\step\models\StepUser;
use app\plugins\step\Plugin;

class OrderPayedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            /** @var OrderEvent $event */
            // 步数宝商品支付成功
            if ($event->order->sign !== (new Plugin())->getName()) {
                return true;
            }
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $orderDetail = OrderDetail::find()->where(['order_id' => $event->order->id])->with('goods')->one();
                if (!$orderDetail) {
                    throw new \Exception('订单详情不存在');
                }
                $stepUser = StepUser::findOne([
                    'mall_id' => $event->order->mall_id,
                    'user_id' => $event->order->user_id,
                    'is_delete' => 0
                ]);
                if (!$stepUser) {
                    throw new \Exception('用户不存在');
                }

                $extra = \Yii::$app->serializer->decode($orderDetail->goods_info)['goods_attr']['extra'];

                // 创建步数宝订单
                $stepOrder = new StepOrder();
                $stepOrder->mall_id = $event->order->mall_id;
                $stepOrder->token = $event->order->token;
                $stepOrder->num = $orderDetail->num;
                $stepOrder->user_id = $event->order->user_id;
                $stepOrder->total_pay_price = $event->order->total_pay_price;
                $stepOrder->order_id = $event->order->id;
                $stepOrder->currency = $extra['step_currency'];
                $stepOrder->is_delete = 0;
                if (!$stepOrder->save()) {
                    throw new \Exception('创建步数宝订单失败');
                }

                (new CommonCurrencyModel())->setUser($stepUser)->sub(floatval($stepOrder->currency), $orderDetail->goods->name, '步数宝订单号' . $event->order->order_no);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        });
    }
}
