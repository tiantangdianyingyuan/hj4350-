<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/12/2
 * Time: 13:54
 */

namespace app\plugins\vip_card\forms\api;

use app\forms\api\order\OrderPayFormBase;
use app\forms\api\order\OrderPayNotify;
use app\core\payment\PaymentOrder;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;

class OrderPayForm extends OrderPayFormBase
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
        /** @var Order[] $order */
        $order = Order::find()->where([
            'token' => $this->token,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
        ])->one();
        if (!$order) {
            $orderSubmitResult = OrderSubmitResult::findOne([
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
        return $this->getReturnData($order);
    }

    /**
     * @param Order $order
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public function getReturnData($order)
    {
        $vipCardSetting = (new CommonVipCardSetting())->getSetting();
        $supportPayTypes = $this->getSupportPayTypes($vipCardSetting);
        $payOrder = new PaymentOrder([
            'title' => 'SVIP',
            'amount' => floatval($order->total_pay_price),
            'orderNo' => $order->order_no,
            'notifyClass' => OrderPayNotify::class,
            'supportPayTypes' => $supportPayTypes,
        ]);
        $id = \Yii::$app->payment->createOrder($payOrder);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }

    private function getSupportPayTypes($vipCardSetting)
    {
        $arr = [];
        foreach ($vipCardSetting['payment_type'] as $item) {
            if ($item == 'online_pay') {
                $arr[] = \app\core\payment\Payment::PAY_TYPE_WECHAT;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_ALIPAY;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_BAIDU;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_TOUTIAO;
            }
            if ($item == 'balance') {
                $arr[] = \app\core\payment\Payment::PAY_TYPE_BALANCE;
            }
        }

        return $arr;
    }
}