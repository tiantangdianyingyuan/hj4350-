<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 10:46
 */


namespace app\plugins\gift\forms\api;


use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\models\Model;
use app\plugins\gift\models\GiftOrderSubmitResult;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;

class GiftOrderPayForm extends Model
{
    public $queue_id;
    public $token;

    public function rules()
    {
        return [
            [['queue_id', 'token'], 'required'],
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        if (!\Yii::$app->queue->isDone($this->queue_id)) {
            return [
                'code' => 0,
                'data' => [
                    'retry' => 1,
                ],
            ];
        }
        /** @var GiftSendOrder[] $orders */
        $orders = GiftSendOrder::find()->where([
            'token' => $this->token,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
        ])->all();
        if (!$orders || !count($orders)) {
            $orderSubmitResult = GiftOrderSubmitResult::findOne([
                'token' => $this->token,
            ]);
            if ($orderSubmitResult) {
                return [
                    'code' => 1,
                    'msg' => $orderSubmitResult->data,
                ];
            }
            return [
                'code' => 1,
                'msg' => '订单不存在或已失效。',
            ];
        }
        return $this->getReturnData($orders);
    }

    /**
     * @param GiftSendOrder[] $orders
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public function getReturnData($orders)
    {
        $hasMchOrder = false;
        foreach ($orders as $order) {
            if ($order->mch_id != 0) {
                $hasMchOrder = true;
                break;
            }
        }
        $supportPayTypes = (array)$order->decodeSupportPayTypes($order->support_pay_types);
        if (!count($supportPayTypes)) {
            $supportPayTypes = [
                Payment::PAY_TYPE_BALANCE,
                Payment::PAY_TYPE_WECHAT,
                Payment::PAY_TYPE_ALIPAY,
            ];
        }
        if ($hasMchOrder && isset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO])) {
            unset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO]);
        }
        $paymentOrders = [];
        $gift_id = 0;
        foreach ($orders as $order) {
            $paymentOrder = new PaymentOrder([
                'title' => $this->getOrderTitle($order),
                'amount' => (float)$order->total_pay_price,
                'orderNo' => $order->order_no,
                'notifyClass' => GiftOrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $paymentOrders[] = $paymentOrder;
            $gift_id = $order->gift_id;
        }
        $id = \Yii::$app->payment->createOrder($paymentOrders);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
                'gift_id' => $gift_id
            ],
        ];
    }

    /**
     * @param GiftSendOrder $order
     * @return string
     */
    private function getOrderTitle($order)
    {
        /** @var GiftSendOrderDetail[] $details */
        $details = $order->getDetail()->andWhere(['is_delete' => 0])->with('goods')->all();
        if (!$details || !is_array($details) || !count($details)) {
            return $order->order_no;
        }
        $titles = [];
        foreach ($details as $detail) {
            if (!$detail->goods) {
                continue;
            }
            $titles[] = $detail->goods->name;
        }
        $title = implode(';', $titles);
        if (mb_strlen($title) > 26) {
            return '礼物订单-' . mb_substr($title, 0, 26);
        } else {
            return '礼物订单-' . $title;
        }
    }
}
