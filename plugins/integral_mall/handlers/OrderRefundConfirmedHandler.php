<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\integral_mall\handlers;

use app\core\currency\IntegralModel;
use app\events\OrderRefundEvent;
use app\handlers\HandlerBase;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserInfo;
use app\plugins\integral_mall\models\IntegralMallOrders;
use app\plugins\integral_mall\models\Order;
use app\plugins\integral_mall\Plugin;

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
                }
                /** @var OrderRefundEvent $event */
                // 商家同意退款 退回积分
                // 退回积分
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $integralOrder = IntegralMallOrders::find()->where([
                        'order_id' => $order->id,
                        'mall_id' => $order->mall_id
                    ])->one();

                    if (!$integralOrder) {
                        throw new \Exception('积分商城:订单不存在,id=>' . $order->id);
                    }

                    $userInfo = UserInfo::find()->where([
                        'user_id' => $order->user_id
                    ])->one();
                    if (!$userInfo) {
                        throw new \Exception('积分商城:用户信息不存在');
                    }

                    $user = User::findOne($order->user_id);
                    \Yii::$app->currency->setUser($user)->integral->refund(
                        (int)$integralOrder->integral_num,
                        "积分商城:订单取消积分退回",
                        $integralOrder->token
                    );

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        });
    }
}
