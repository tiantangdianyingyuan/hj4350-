<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;


use app\core\response\ApiCode;
use app\jobs\OrderCancelJob;
use app\models\Model;
use app\models\Order;
use app\models\PaymentOrder;

class OrderForm extends Model
{
    public $pay_id;

    public function rules()
    {
        return [
            [['pay_id'], 'required'],
            [['pay_id'], 'integer']
        ];
    }

    public function orderCancel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var PaymentOrder $paymentOrder */
            $paymentOrder = PaymentOrder::find()->where(['payment_order_union_id' => $this->pay_id])->one();
            if (!$paymentOrder) {
                throw new \Exception($this->getErrorMsg($this->getErrorMsg($paymentOrder)));
            }
            /** @var Order $order */
            $order = Order::find()->where(['order_no' => $paymentOrder->order_no])->one();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $queueId = \Yii::$app->queue->delay(0)->push(new OrderCancelJob([
                'orderId' => $order->id
            ]));

            $isDone = true;
            while ($isDone) {
                if (\Yii::$app->queue->isDone($queueId)) {
                    $isDone = false;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}