<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\events\OrderRefundEvent;
use app\models\Model;
use app\models\OrderRefund;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;

class OrderRefundForm extends Model
{
    public $refund_order_id;

    public function rules()
    {
        return [
            [['refund_order_id'], 'integer'],
        ];
    }

    public function shouHuo()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 2,
                'is_send' => 1,
                'id' => $this->refund_order_id,
            ])
                ->with('order')
                ->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            if ($orderRefund->order->send_type != 1 && $orderRefund->order->send_type != 2 && $orderRefund->type != 1 && $orderRefund->type != 2) {
                throw new \Exception('售后订单异常');
            }

            $orderRefund->is_confirm = 1;
            $orderRefund->confirm_time = mysql_timestamp();
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }
            // 有公用方法
            $res = $this->updatePayStatus($orderRefund);

            if (!$orderRefund->type == 1) {
                \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
                    'order_refund' => $orderRefund,
                    'advance_refund' => 0,
                ]));
            } else {
                \Yii::warning('退款再触发');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功',
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    // 货到付款 退款时需将
    private function updatePayStatus($orderRefund)
    {
        if ($orderRefund->order->pay_type == 2) {
            $paymentOrder = PaymentOrder::find()
                ->andWhere(['order_no' => $orderRefund->order->order_no])
                ->andWhere(['!=', 'pay_type', 0])
                ->one();
            if (!$paymentOrder) {
                throw new \Exception('支付订单不存在');
            }
            $paymentOrder->is_pay = 1;
            $res = $paymentOrder->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($paymentOrder));
            }

            $paymentOrderUnion = PaymentOrderUnion::find()->andWhere(['id' => $paymentOrder->payment_order_union_id])->one();

            if (!$paymentOrderUnion) {
                throw new \Exception('商户支付订单不存在');
            }
            $paymentOrderUnion->is_pay = 1;
            $res = $paymentOrderUnion->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($paymentOrderUnion));
            }
        }
    }
}
