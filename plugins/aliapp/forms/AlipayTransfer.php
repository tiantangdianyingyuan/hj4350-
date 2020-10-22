<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/19
 * Time: 11:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\aliapp\forms;

use Alipay\AlipayRequestFactory;
use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\core\payment\PaymentException;
use app\forms\common\transfer\BaseTransfer;
use app\models\AliappConfig;


class AlipayTransfer extends BaseTransfer
{
    /**
     * @param \app\models\PaymentTransfer $paymentTransfer
     * @param \app\models\User $user
     * @return bool|mixed
     * @throws PaymentException
     */
    public function transfer($paymentTransfer, $user)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $aliappConfig = AliappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
            if (!$aliappConfig || empty($aliappConfig['transfer_app_id']) || empty($aliappConfig['transfer_app_private_key']) || empty($aliappConfig['transfer_alipay_public_key']) || empty($aliappConfig['transfer_appcert']) || empty($aliappConfig['transfer_alipay_rootcert'])) {
                throw new \Exception('支付宝小程序转账尚未配置。');
            }
            $aop = new AopClient(
                $aliappConfig->transfer_app_id,
                AlipayKeyPair::create($aliappConfig->transfer_app_private_key, $aliappConfig->transfer_alipay_public_key)
            );
            $request = AlipayRequestFactory::create('alipay.fund.trans.toaccount.transfer',[
                'biz_content' => [
                    'out_biz_no' => $paymentTransfer->order_no,
                    'payee_type' => 'ALIPAY_USERID',
                    'payee_account' => $user->userInfo->platform_user_id,
                    'amount' => $paymentTransfer->amount
                ],
                'app_cert_sn' => CertSN::getSn($aliappConfig->transfer_appcert),
                'alipay_root_cert_sn' => CertSN::getSn($aliappConfig->transfer_alipay_rootcert,true),
            ]);
            $res = $aop->execute($request)->getData();
            if ($res['code'] != 10000) {
                throw new \Exception($res['sub_msg'], $res);
            }
            $paymentTransfer->is_pay = 1;
            if (!$paymentTransfer->save()) {
                throw new \Exception($this->getErrorMsg($paymentTransfer));
            }
            $t->commit();
            return true;
        } catch (\Exception $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}
