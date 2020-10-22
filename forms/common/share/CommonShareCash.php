<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/26
 * Time: 17:20
 */

namespace app\forms\common\share;


use app\core\payment\PaymentTransfer;
use app\models\Model;
use app\models\Share;
use app\models\ShareCash;
use app\models\User;
use yii\db\Exception;

/**
 * @property ShareCash $shareCash
 * @property User $user
 */
class CommonShareCash extends Model
{
    public $shareCash;
    public $user;

    public $actual_price;

    /**
     * @return mixed
     * @throws Exception
     * 打款
     */
    public function remit()
    {
        $this->user = User::find()
            ->where(['id' => $this->shareCash->user_id, 'is_delete' => 0, 'mall_id' => $this->shareCash->mall_id])
            ->with('userInfo')->one();

        $serviceCharge = round($this->shareCash->price * $this->shareCash->service_charge / 100, 2);
        $this->actual_price = round($this->shareCash->price - $serviceCharge, 2);

        $type = $this->shareCash->type;
        if (method_exists($this, $type)) {
            return $this->$type();
        } else {
            throw new Exception('错误的提现方式', $this->shareCash);
        }
    }

    // 微信手动打款
    private function wechat()
    {
        return true;
    }

    // 支付宝手动打款
    private function alipay()
    {
        return true;
    }

    // 银行手动打款
    private function bank()
    {
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \app\core\payment\PaymentException
     * 根据用户身份自动打款（微信/支付宝）
     */
    private function auto()
    {
        $paymentTransfer = new PaymentTransfer([
            'orderNo' => $this->shareCash->order_no,
            'amount' => round($this->actual_price, 2),
            'user' => $this->user,
            'title' => '分销商提现',
            'transferType' => $this->user->userInfo->platform
        ]);
        \Yii::$app->payment->transfer($paymentTransfer);
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     * 打款的余额
     */
    private function balance()
    {
        $desc = "分销商提现到余额，提现金额：{$this->shareCash->price},提现手续费：{$this->shareCash->service_charge}%";
        \Yii::$app->currency->setUser($this->user)->balance->add($this->actual_price, $desc);
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     * 驳回
     */
    public function reject()
    {
        $this->user = User::find()
            ->where(['id' => $this->shareCash->user_id, 'is_delete' => 0, 'mall_id' => $this->shareCash->mall_id])
            ->with('userInfo')->one();
        $desc = "分销商提现退回，退回佣金：{$this->shareCash->price}";
        \Yii::$app->currency->setUser($this->user)
            ->brokerage->refund(round($this->shareCash->price, 2), $desc);
        return true;
    }
}
