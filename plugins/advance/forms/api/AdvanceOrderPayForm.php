<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 10:46
 */


namespace app\plugins\advance\forms\api;


use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Model;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\AdvanceOrderSubmitResult;

class AdvanceOrderPayForm extends Model
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
        /** @var AdvanceOrder $order */
        $order = AdvanceOrder::find()->where([
            'token' => $this->token,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
        ])->one();
        if (!$order) {
            $orderSubmitResult = AdvanceOrderSubmitResult::findOne([
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
     * @param AdvanceOrder $order
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public function getReturnData($order)
    {
        //获取支付方式
        $supportPayTypes = [];
        $setting = (new SettingForm())->search();
        if (in_array('online_pay', (array)$setting['deposit_payment_type'])) {
            $supportPayTypes[] = Payment::PAY_TYPE_WECHAT;
            $supportPayTypes[] = Payment::PAY_TYPE_ALIPAY;
            $supportPayTypes[] = Payment::PAY_TYPE_BAIDU;
            $supportPayTypes[] = Payment::PAY_TYPE_TOUTIAO;
        }
        if (in_array('balance', (array)$setting['deposit_payment_type'])) {
            $supportPayTypes[] = Payment::PAY_TYPE_BALANCE;
        }


        $paymentOrders = [];
        $paymentOrder = new PaymentOrder([
            'title' => $this->getOrderTitle($order),
            'amount' => (float)floatval(bcmul($order->deposit, $order->goods_num)),
            'orderNo' => $order->advance_no,
            'notifyClass' => AdvanceOrderPayNotify::class,
            'supportPayTypes' => $supportPayTypes,
        ]);
        $paymentOrders[] = $paymentOrder;
        $id = \Yii::$app->payment->createOrder($paymentOrders);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @param AdvanceOrder $order
     * @return bool|string
     */
    private function getOrderTitle($order)
    {
        $title = Goods::findOne(['id' => $order->goods_id])->getName();
        if (mb_strlen($title) > 26) {
            return '定金订单-' . mb_substr($title, 0, 26);
        } else {
            return '定金订单-' . $title;
        }
    }
}
