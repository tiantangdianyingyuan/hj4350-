<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/18
 * Time: 17:10
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\aliapp\forms;


use Alipay\AlipayRequestFactory;
use app\core\payment\PaymentException;
use app\forms\common\refund\BaseRefund;
use app\models\PaymentRefund;
use app\plugins\aliapp\Plugin;

class AlipayRefund extends BaseRefund
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
            $plugin = new Plugin();
            $aop = $plugin->getAliAopClient();
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
        $paymentRefund->pay_type = 4;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
