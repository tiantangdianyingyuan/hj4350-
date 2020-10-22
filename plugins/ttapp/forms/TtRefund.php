<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/6
 * Time: 16:28
 */

namespace app\plugins\ttapp\forms;

use Alipay\AlipayRequestFactory;
use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\core\payment\PaymentException;
use app\forms\common\refund\BaseRefund;
use app\helpers\CurlHelper;
use app\models\Model;
use app\models\PaymentRefund;
use app\plugins\bdapp\models\BdappConfig;
use app\plugins\bdapp\models\BdappOrder;
use app\plugins\bdapp\Plugin;
use app\plugins\ttapp\models\TtappConfig;

class TtRefund extends BaseRefund
{
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $ttappConfig = TtappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
            if (!$ttappConfig) {
                throw new \Exception('头条小程序支付尚未配置。');
            }
            $aop = new AopClient(
                $ttappConfig->alipay_app_id,
                AlipayKeyPair::create($ttappConfig->alipay_private_key, $ttappConfig->alipay_public_key)
            );

            $request = AlipayRequestFactory::create('alipay.trade.refund', [
                'biz_content' => [
                    'out_trade_no' => $paymentOrderUnion->order_no,
                    'refund_amount' => $paymentRefund->amount,
                    'out_request_no' => $paymentRefund->order_no,
                ]
            ]);
            $res = $aop->execute($request)->getData();
            if ($res['code'] != 10000) {
                throw new \Exception($res['sub_msg']);
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
        $paymentRefund->pay_type = 6;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
