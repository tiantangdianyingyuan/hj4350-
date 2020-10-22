<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\recharge;


use app\core\payment\PaymentOrder;
use app\core\response\ApiCode;
use app\models\Model;
use app\models\Order;
use app\models\Recharge;
use app\models\RechargeOrders;
use yii\db\Exception;

class RechargeOrderForm extends Model
{
    public $pay_price;
    public $send_price;
    public $id;

    public function rules()
    {
        return [
            [['pay_price', 'send_price'], 'required'],
            [['pay_price', 'send_price'], 'double'],
            [['id'], 'integer'],
        ];
    }

    public function balanceRecharge()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $order = new RechargeOrders();
            $order->mall_id = \Yii::$app->mall->id;
            $order->order_no = Order::getOrderNo('RE');
            $order->user_id = \Yii::$app->user->id;
            if ($this->id) {
                $recharge = Recharge::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                if (!$recharge) {
                    throw new Exception('充值方案错误');
                }
                $order->pay_price = $recharge->pay_price;
                $order->send_price = $recharge->send_price;
                $order->send_integral = $recharge->send_integral;
                $order->send_member_id = $recharge->send_member_id;
            } else {
                $order->pay_price = $this->pay_price;
                $order->send_price = 0;
                $order->send_integral = 0;
                $order->send_member_id = 0;
            }
            $order->pay_type = RechargeOrders::PAY_TYPE_ON_LINE;
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            $payOrder = new PaymentOrder([
                'title' => '余额充值',
                'amount' => floatval($order->pay_price),
                'orderNo' => $order->order_no,
                'notifyClass' => RechargePayNotify::class,
                'supportPayTypes' => [ //选填，支持的支付方式，若不填将支持所有支付方式。
                    \app\core\payment\Payment::PAY_TYPE_WECHAT,
                    \app\core\payment\Payment::PAY_TYPE_ALIPAY,
                    \app\core\payment\Payment::PAY_TYPE_BAIDU,
                    \app\core\payment\Payment::PAY_TYPE_TOUTIAO,
                ],
            ]);
            $id = \Yii::$app->payment->createOrder($payOrder);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单创建成功',
                'data' => [
                    'pay_id' => $id
                ]
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
