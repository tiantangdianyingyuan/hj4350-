<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/6
 * Time: 16:28
 */

namespace app\plugins\bdapp\forms;

use app\core\payment\PaymentException;
use app\forms\common\refund\BaseRefund;
use app\helpers\CurlHelper;
use app\models\Model;
use app\models\PaymentRefund;
use app\plugins\bdapp\models\BdappConfig;
use app\plugins\bdapp\models\BdappOrder;
use app\plugins\bdapp\Plugin;

class BdRefund extends BaseRefund
{
    /**
     * @param PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $config = BdappConfig::findOne([
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$config || !$config->pay_app_key) {
                throw new \Exception('百度小程序尚未配置。');
            }

            $bdAppOrder = BdappOrder::findOne(['order_no' => $paymentRefund->out_trade_no]);
            if (!$bdAppOrder) {
                throw new \Exception('百度订单号获取失败');
            }

            $api = "https://nop.nuomi.com/nop/server/rest";

            $paramsCancel = [
                'method' => 'nuomi.cashier.syncorderstatus',
                'orderId' => $bdAppOrder->bd_order_id,
                'userId' => $bdAppOrder->bd_user_id,
                'type' => 3,
                'appKey' => $config->pay_app_key,
            ];

            $rsaSign = RsaSign::genSignWithRsa($paramsCancel, $config->pay_private_key);
            $paramsCancel['rsaSign'] = $rsaSign;
            $res = CurlHelper::getInstance()->httpPost($api,[],$paramsCancel);
            if ($res['errno'] != 0) {
                throw new \Exception($res['msg']);
            }

            $params = [
                'method' => 'nuomi.cashier.applyorderrefund',
                'orderId' => $bdAppOrder->bd_order_id,
                'userId' => $bdAppOrder->bd_user_id,
                'refundType' => 1,
                'refundReason' => '订单退款',
                'tpOrderId' => $paymentRefund->out_trade_no,
                'appKey' => $config->pay_app_key,
                'applyRefundMoney' => $paymentRefund->amount * 100,
                'bizRefundBatchId' => $paymentRefund->order_no
            ];
            $rsaSign = RsaSign::genSignWithRsa($params, $config->pay_private_key);
            $params['rsaSign'] = $rsaSign;

            $res = CurlHelper::getInstance()->httpPost($api,[],$params);
            if ($res['errno'] != 0) {
                throw new \Exception($res['msg']);
            }

            $bdAppOrder->bd_refund_batch_id = $res['data']['refundBatchId'];
            $bdAppOrder->bd_refund_money = $res['data']['refundPayMoney'];
            $bdAppOrder->refund_money = $paymentRefund->amount * 100;
            if (!$bdAppOrder->save()) {
                throw new \Exception((new Model())->getErrorMsg($bdAppOrder));
            }

            $this->save($paymentRefund);
            $t->commit();
            return true;
        } catch (\Exception $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * @param PaymentRefund $paymentRefund
     * @throws \Exception
     */
    private function save($paymentRefund)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 5;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
