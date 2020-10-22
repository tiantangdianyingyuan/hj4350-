<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/11 15:18
 */


namespace app\controllers\api;


use app\core\payment\Payment;
use app\core\response\ApiCode;
use app\models\PaymentOrderUnion;

class PaymentController extends ApiController
{
    /**
     * @param integer $id PaymentOrderUnion id
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionGetPayments($id)
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'id' => $id,
        ]);
        if (!$paymentOrderUnion) {
            return $this->asJson([
                'code' => ApiCode::CODE_ERROR,
                'msg' => '待支付订单不存在。',
            ]);
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        $payments = [
            Payment::PAY_TYPE_BALANCE,
            Payment::PAY_TYPE_WECHAT,
            Payment::PAY_TYPE_ALIPAY,
            Payment::PAY_TYPE_BAIDU,
            Payment::PAY_TYPE_TOUTIAO,
            Payment::PAY_TYPE_HUODAO,
        ];
        $resultPayments = [];
        $iconBaseUrl = \Yii::$app->request->hostInfo . '/' . \Yii::$app->request->baseUrl . '/statics/img/app/common/';
        foreach ($payments as $payment) {
            switch ($payment) {
                case Payment::PAY_TYPE_BALANCE:
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_BALANCE, $supportPayTypes)) {
                        break;
                    }
                    if (!\Yii::$app->payment->isRechargeOpen()) {
                        break;
                    }
                    $balanceAmount = \Yii::$app->currency->setUser(\Yii::$app->user->identity)->balance->select();
                    if ($balanceAmount < $paymentOrderUnion->amount) {
                        $disabled = true;
                        $desc = '账户余额不足';
                    } else {
                        $disabled = false;
                        $desc = '账户余额: ' . price_format($balanceAmount) . '元';
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_BALANCE,
                        'name' => '余额支付',
                        'desc' => $desc,
                        'disabled' => $disabled,
                        'icon' => $iconBaseUrl . 'payment-balance.png',
                    ];
                    break;
                case Payment::PAY_TYPE_WECHAT:
                    if ($paymentOrderUnion->amount == 0 || \Yii::$app->appPlatform != APP_PLATFORM_WXAPP) {
                        break;
                    }
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_WECHAT, $supportPayTypes)) {
                        break;
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_WECHAT,
                        'name' => '微信支付',
                        'desc' => null,
                        'disabled' => false,
                        'icon' => $iconBaseUrl . 'payment-wechat.png',
                    ];
                    break;
                case Payment::PAY_TYPE_ALIPAY:
                    if ($paymentOrderUnion->amount == 0 || \Yii::$app->appPlatform != APP_PLATFORM_ALIAPP) {
                        break;
                    }
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_ALIPAY, $supportPayTypes)) {
                        break;
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_ALIPAY,
                        'name' => '支付宝',
                        'desc' => null,
                        'disabled' => false,
                        'icon' => $iconBaseUrl . 'payment-alipay.png',
                    ];
                    break;
                case Payment::PAY_TYPE_BAIDU:
                    if ($paymentOrderUnion->amount == 0 || \Yii::$app->appPlatform != APP_PLATFORM_BDAPP) {
                        break;
                    }
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_BAIDU, $supportPayTypes)) {
                        break;
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_BAIDU,
                        'name' => '百度收银台',
                        'desc' => null,
                        'disabled' => false,
                        'icon' => $iconBaseUrl . 'payment-baidu.png',
                    ];
                    break;
                case Payment::PAY_TYPE_TOUTIAO:
                    if ($paymentOrderUnion->amount == 0 || \Yii::$app->appPlatform != APP_PLATFORM_TTAPP) {
                        break;
                    }
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_TOUTIAO, $supportPayTypes)) {
                        break;
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_TOUTIAO,
                        'name' => '支付宝',
                        'desc' => null,
                        'disabled' => false,
                        'icon' => $iconBaseUrl . 'payment-alipay.png',
                    ];
                    break;
                case 'huodao':
                    if ($paymentOrderUnion->amount == 0) {
                        break;
                    }
                    if (!empty($supportPayTypes) && !in_array(Payment::PAY_TYPE_HUODAO, $supportPayTypes)) {
                        break;
                    }
                    $resultPayments[] = [
                        'key' => Payment::PAY_TYPE_HUODAO,
                        'name' => '货到付款',
                        'desc' => null,
                        'disabled' => false,
                        'icon' => $iconBaseUrl . 'payment-huodao.png',
                    ];
                    break;
                default:
                    break;
            }
        }
        if (count($resultPayments)) {
            foreach ($resultPayments as $i => $payment) {
                if (!$payment['disabled']) {
                    $resultPayments[$i]['checked'] = true;
                    break;
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'amount' => price_format($paymentOrderUnion->amount),
                'list' => $resultPayments,
            ],
        ];
    }

    /**
     * @param integer $id PaymentOrderUnion id
     * @param string $pay_type
     * @return array
     * @throws \app\core\payment\PaymentException
     */
    public function actionPayData($id, $pay_type)
    {
        $payment = new Payment();
        $payData = $payment->getPayData($id, $pay_type);
        return [
            'code' => 0,
            'data' => $payData,
        ];
    }

    public function actionPayBuyBalance($id)
    {
        try {
            \Yii::$app->payment->payBuyBalance($id);
            return [
                'code' => 0,
                'msg' => '支付成功。',
            ];
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function actionPayBuyHuodao($id)
    {
        try {
            \Yii::$app->payment->payBuyHuodao($id);
            return [
                'code' => 0,
                'msg' => '下单成功。',
            ];
        } catch (\Exception $e) {
            return [
                'code' => 1,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
