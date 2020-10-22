<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 16:11
 */


namespace app\controllers\system;


use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\controllers\Controller;
use app\core\payment\PaymentNotify;
use app\models\Mall;
use app\models\Model;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\plugins\bdapp\models\BdappOrder;
use app\plugins\wxapp\forms\Enum;
use luweiss\Wechat\WechatHelper;
use luweiss\Wechat\WechatPay;
use yii\web\Response;

class PayNotifyController extends Controller
{
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    public function actionWechat()
    {
        \Yii::$app->response->format = Response::FORMAT_XML;
        $xml = \Yii::$app->request->rawBody;
        $res = WechatHelper::xmlToArray($xml);
        if (!$res) {
            throw new \Exception('请求数据错误: ' . $xml);
        }
        if (empty($res['out_trade_no'])
            || empty($res['sign'])
            || empty($res['total_fee'])
            || empty($res['result_code'])
            || empty($res['return_code'])
        ) {
            throw new \Exception('请求数据错误: ' . $xml);
        }

        if ($res['result_code'] !== 'SUCCESS' || $res['return_code'] !== 'SUCCESS') {
            throw new \Exception('订单尚未支付: ' . $xml);
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['out_trade_no'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['out_trade_no']);
        }
        if ($paymentOrderUnion->app_version) {
            \Yii::$app->setAppVersion($paymentOrderUnion->app_version);
        }
        if ($paymentOrderUnion->is_pay === 1) {
            $responseData = [
                'return_code' => 'SUCCESS',
                'return_msg' => 'OK',
            ];
            \Yii::$app->response->format = Response::FORMAT_XML;
            echo WechatHelper::arrayToXml($responseData);
            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        /** @var WechatPay $wechatPay */
        $wechatPay = \Yii::$app->plugin->getPlugin('wxapp')->getWechatPay(Enum::WECHAT_PAY_SERVICE);

        $truthSign = $wechatPay->makeSign($res);
        if ($truthSign !== $res['sign']) {
            throw new \Exception('签名验证失败。');
        }

        $paymentOrderUnionAmount = (doubleval($paymentOrderUnion->amount) * 100) . '';
        if (intval($res['total_fee']) !== intval($paymentOrderUnionAmount)) {
            throw new \Exception('支付金额与订单金额不一致。');
        }

        $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
        $paymentOrderUnion->is_pay = 1;
        $paymentOrderUnion->pay_type = 1;
        if (!$paymentOrderUnion->save()) {
            throw new \Exception($paymentOrderUnion->getFirstErrors());
        }
        foreach ($paymentOrders as $paymentOrder) {
            $Class = $paymentOrder->notify_class;
            if (!class_exists($Class)) {
                continue;
            }
            $paymentOrder->is_pay = 1;
            $paymentOrder->pay_type = 1;
            if (!$paymentOrder->save()) {
                throw new \Exception($paymentOrder->getFirstErrors());
            }
            /** @var PaymentNotify $notify */
            $notify = new $Class();
            try {
                $po = new \app\core\payment\PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_WECHAT
                ]);
                $notify->notify($po);
            } catch (\Exception $e) {
            }
        }
        $responseData = [
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
        ];
        \Yii::$app->response->format = Response::FORMAT_XML;
        echo WechatHelper::arrayToXml($responseData);
        return;
    }

    public function actionAlipay()
    {
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['out_trade_no'])
            || empty($res['sign'])
            || empty($res['total_amount'])
        ) {
            throw new \Exception('请求数据错误');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['out_trade_no'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['out_trade_no']);
        }
        if ($paymentOrderUnion->app_version) {
            \Yii::$app->setAppVersion($paymentOrderUnion->app_version);
        }
        if ($paymentOrderUnion->is_pay === 1) {

            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $passed = \Yii::$app->plugin->getPlugin('aliapp')->checkSign();

        if ($passed) {
            $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 4;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $Class = $paymentOrder->notify_class;
                if (!class_exists($Class)) {
                    continue;
                }
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 4;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                /** @var PaymentNotify $notify */
                $notify = new $Class();
                try {
                    $po = new \app\core\payment\PaymentOrder([
                        'orderNo' => $paymentOrder->order_no,
                        'amount' => (float)$paymentOrder->amount,
                        'title' => $paymentOrder->title,
                        'notifyClass' => $paymentOrder->notify_class,
                        'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_ALIPAY
                    ]);
                    $notify->notify($po);
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
            echo "success";
            return;
        }
    }

    public function actionBaidu()
    {
        \Yii::error('百度支付回调');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['tpOrderId'])
            || empty($res['rsaSign'])
            || empty($res['totalMoney'])
            || empty($res['orderId'])
        ) {
            throw new \Exception('请求数据错误');
        }

        if ($res['status'] != 2) {
            throw new \Exception('订单尚未支付');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['tpOrderId'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['tpOrderId']);
        }
        if ($paymentOrderUnion->app_version) {
            \Yii::$app->setAppVersion($paymentOrderUnion->app_version);
        }

        $bdAppOrder = BdappOrder::findOne(['order_no' => $res['tpOrderId']]);
        if (!$bdAppOrder) {
            $bdAppOrder = new BdappOrder();
            $bdAppOrder->order_no = $res['tpOrderId'];
            $bdAppOrder->bd_order_id = $res['orderId'];
            $bdAppOrder->bd_user_id = $res['userId'];
            $bdAppOrder->save();
        } else {
            $bdAppOrder->bd_user_id = $res['userId'];
            $bdAppOrder->save();
        }

        if ($paymentOrderUnion->is_pay === 1) {
            $responseData = [
                'errno' => 0,
                'msg' => 'success',
                'data' => ['isConsumed' => 2]
            ];
            \Yii::$app->response->data = $responseData;
            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $res['sign'] = $res['rsaSign'];
        unset($res['rsaSign']);
        $truthSign = \Yii::$app->plugin->getPlugin('bdapp')->checkSignWithRsa($res);

        if (!$truthSign) {
            throw new \Exception('签名验证失败。');
        }

        $paymentOrderUnionAmount = (doubleval($paymentOrderUnion->amount) * 100) . '';
        if (intval($res['totalMoney']) !== intval($paymentOrderUnionAmount)) {
            throw new \Exception('支付金额与订单金额不一致。');
        }

        $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
        $paymentOrderUnion->is_pay = 1;
        $paymentOrderUnion->pay_type = 5;
        if (!$paymentOrderUnion->save()) {
            throw new \Exception($paymentOrderUnion->getFirstErrors());
        }
        foreach ($paymentOrders as $paymentOrder) {
            $Class = $paymentOrder->notify_class;
            if (!class_exists($Class)) {
                continue;
            }
            $paymentOrder->is_pay = 1;
            $paymentOrder->pay_type = 5;
            if (!$paymentOrder->save()) {
                throw new \Exception($paymentOrder->getFirstErrors());
            }
            /** @var PaymentNotify $notify */
            $notify = new $Class();
            try {
                $po = new \app\core\payment\PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_BAIDU
                ]);
                $notify->notify($po);
            } catch (\Exception $e) {
                \Yii::error($e);
            }
        }
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => ['isConsumed' => 2]
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionBaiduRefundVerify()
    {
        \Yii::error('百度退款审核');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['orderId'])
            || empty($res['userId'])
            || empty($res['tpOrderId'])
            || empty($res['refundBatchId'])
        ) {
            throw new \Exception('请求数据错误');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['tpOrderId'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['tpOrderId']);
        }
        if ($paymentOrderUnion->app_version) {
            \Yii::$app->setAppVersion($paymentOrderUnion->app_version);
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $res['sign'] = $res['rsaSign'];
        unset($res['rsaSign']);
        $truthSign = \Yii::$app->plugin->getPlugin('bdapp')->checkSignWithRsa($res);

        if (!$truthSign) {
            throw new \Exception('退款查询签名验证失败。');
        }

        $bdAppOrder = BdappOrder::findOne(['bd_order_id' => $res['orderId']]);
        if (!$bdAppOrder) {
            throw new \Exception('退款订单错误.');
        }

        \Yii::error('百度退款审核成功');
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => ['auditStatus' => 1,
                'calculateRes' => [
                    'refundPayMoney' => $res['applyRefundMoney']
                ]]
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionBaiduRefund()
    {
        \Yii::error('百度退款回调');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        try {
            $bdAppOrder = BdappOrder::findOne(['bd_order_id' => $res['orderId']]);
            if (!$bdAppOrder) {
                throw new \Exception('百度订单号获取失败');
            }
            $bdAppOrder->is_refund = 1;
            $res = $bdAppOrder->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($bdAppOrder));
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => (object)null,
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionToutiao()
    {
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['out_trade_no'])
            || empty($res['sign'])
            || empty($res['total_amount'])
        ) {
            throw new \Exception('请求数据错误');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['out_trade_no'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['out_trade_no']);
        }
        if ($paymentOrderUnion->app_version) {
            \Yii::$app->setAppVersion($paymentOrderUnion->app_version);
        }
        if ($paymentOrderUnion->is_pay === 1) {

            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $passed = \Yii::$app->plugin->getPlugin('ttapp')->checkSign();

        if ($passed) {
            $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 6;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $Class = $paymentOrder->notify_class;
                if (!class_exists($Class)) {
                    continue;
                }
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 6;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                /** @var PaymentNotify $notify */
                $notify = new $Class();
                try {
                    $po = new \app\core\payment\PaymentOrder([
                        'orderNo' => $paymentOrder->order_no,
                        'amount' => (float)$paymentOrder->amount,
                        'title' => $paymentOrder->title,
                        'notifyClass' => $paymentOrder->notify_class,
                        'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_TOUTIAO
                    ]);
                    $notify->notify($po);
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
            \Yii::$app->response->data = true;
            return true;
        }
    }

    public function actionCityService()
    {
        \Yii::warning('同城配送接口回调测试');
    }
}
