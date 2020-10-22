<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/19
 * Time: 11:34
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\forms;


use app\core\payment\PaymentException;
use app\forms\common\transfer\BaseTransfer;
use app\plugins\wxapp\Plugin;
use luweiss\Wechat\WechatException;

class WechatTransfer extends BaseTransfer
{
    /**
     * @param \app\models\PaymentTransfer $paymentTransfer
     * @param \app\models\User $user
     * @return bool
     * @throws PaymentException
     */
    public function transfer($paymentTransfer, $user)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $plugin = new Plugin();
            $wechatPay = $plugin->getWechatPay();
            $result = $wechatPay->transfers([
                'partner_trade_no' => $paymentTransfer->order_no,
                'openid' => $user->userInfo->platform_user_id,
                'amount' => $paymentTransfer->amount * 100,
                'desc' => '转账',
            ]);
            $paymentTransfer->is_pay = 1;
            if (!$paymentTransfer->save()) {
                throw new \Exception($this->getErrorMsg($paymentTransfer));
            }
            $t->commit();
            return true;
        } catch (WechatException $e) {
            $t->rollBack();
            throw new PaymentException($e->getRaw()['err_code_des']);
        } catch (\Exception $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}
