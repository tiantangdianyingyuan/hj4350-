<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 14:12
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\order;

use app\events\OrderEvent;
use app\forms\OrderConfig;
use app\handlers\orderHandler\OrderHandler;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use yii\helpers\ArrayHelper;

class CommonOrder extends Model
{
    public static function getCommonOrder($sign)
    {
        $self = new self();
        $self->sign = $sign;
        return $self;
    }

    /**
     * @return OrderConfig
     * 获取订单的配置
     */
    public function getOrderConfig()
    {
        $sign = $this->sign;
        try {
            if ($sign) {
                $config = \Yii::$app->plugin->getPlugin($sign)->getOrderConfig();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order config--' . $exception->getMessage());
            $config = new OrderConfig();
            $config->setOrder();
        }
        return $config;
    }

    /**
     * @return OrderHandler
     * 获取订单事件
     */
    public function getOrderHandler()
    {
        $orderHandler = new OrderHandler();
        $orderHandler->sign = $this->sign;
        return $orderHandler;
    }

    /**
     * @param Order $order
     * @throws \Exception
     * 订单确认收货
     */
    public function confirm($order)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($order->is_send != 1) {
                throw new \Exception('订单未发货，无法收货');
            }
            if ($order->is_confirm == 1) {
                throw new \Exception('订单已确认收货,无需重复');
            }
            if ($order->pay_type != 2 && $order->is_pay != 1) {
                throw new \Exception('订单未支付');
            }
            // 货到付款订单 确认收货时即支付
            if ($order->pay_type == 2 && $order->is_pay == 0) {
                $order->is_pay = 1;
                $order->pay_time = mysql_timestamp();
            }
            $order->is_confirm = 1;
            $order->confirm_time = mysql_timestamp();
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }
            $t->commit();
            if ($order->pay_type == 2) {
                // 货到付款的订单 确认收货需要触发支付完成事件
                \Yii::$app->trigger(Order::EVENT_PAYED, new OrderEvent([
                    'order' => $order,
                ]));
            }

            \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent(['order' => $order]));
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    public function getOrderInfoCount()
    {
        if (\Yii::$app->user->isGuest) {
            return [0, 0, 0, 0, 0];
        }
        $form = new CommonOrderList();
        $form->user_id = \Yii::$app->user->id;
        $form->mall_id = \Yii::$app->mall->id;
        $form->is_recycle = 0;

        // TODO 售后状态暂时没加
        // 'is_sale' => 0,
        $form->status = 1;
        $form->getQuery();
        $waitPay = $form->query->count();

        $form->status = 2;
        $form->getQuery();
        $waitSend = $form->query->count();

        $form->status = 3;
        $form->getQuery();
        $waitConfirm = $form->query->count();

        $isComment = (new Mall())->getMallSettingOne('is_comment');
        if ($isComment) {
            $form->status = 9;
            $form->getQuery();
            $waitComment = $form->query->count();
        } else {
            $waitComment = 0;
        }

        $refundList = OrderRefund::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->andWhere([
            'or',
            ['status' => 1],
            ['status' => 2],
        ])
            ->andWhere([
                'or',
                ['is_confirm' => 0],
                ['is_refund' => 0],
                ['is_refund' => 2],
            ])
            ->with('order')
            ->all();

        $newList = [];
        /** @var OrderRefund $item */
        foreach ($refundList as $item) {
            $newItem = ArrayHelper::toArray($item);
            // 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
            if ($item->is_refund == 2) {
                /** @var PaymentOrder $paymentOrder */
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                if (!$paymentRefund) {
                    $newItem['is_refund'] = 0;
                }
            }
            $newList[] = $newItem;
        }
        $waitRefund = 0;
        foreach ($newList as $item) {
            if ($item['type'] == 1 && $item['is_refund'] == 0) {
                $waitRefund += 1;
            }

            if ($item['type'] == 2 && $item['is_confirm'] == 0) {
                $waitRefund += 1;
            }
        }

        return [$waitPay, $waitSend, $waitConfirm, $waitComment, $waitRefund];
    }
}
