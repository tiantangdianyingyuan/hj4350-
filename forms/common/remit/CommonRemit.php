<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/24
 * Time: 16:02
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\remit;


use app\core\payment\PaymentTransfer;
use app\models\Mall;
use app\models\User;
use yii\base\BaseObject;

/**
 * Class CommonRemit
 * @package app\forms\common\remit
 * @property Mall $mall
 * @property User $user
 * @property RemitForm $remitForm
 */
class CommonRemit extends BaseObject
{
    public static $instance;
    public $mall;
    public $user;
    public $remitForm;

    public static function getInstance($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (self::$instance && self::$instance->mall == $mall) {
            return self::$instance;
        }
        self::$instance = new self();
        self::$instance->mall = $mall;
        return self::$instance;
    }

    public function remit()
    {
        $type = $this->remitForm->type;
        if (method_exists($this, $type)) {
            return $this->$type();
        } else {
            throw new \Exception('错误的提现方式', $this->remitForm);
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
     * @throws \Exception
     * @throws \app\core\payment\PaymentException
     * 根据用户身份自动打款（微信/支付宝）
     */
    private function auto()
    {
        $paymentTransfer = new PaymentTransfer([
            'orderNo' => $this->remitForm->orderNo,
            'amount' => round($this->remitForm->amount, 2),
            'user' => $this->user,
            'title' => $this->remitForm->title,
            'transferType' => $this->user->userInfo->platform
        ]);
        \Yii::$app->payment->transfer($paymentTransfer);
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * 打款的余额
     */
    private function balance()
    {
        $remit = $this->remitForm;
        \Yii::$app->currency->setUser($this->user)->balance->add($remit->amount, $remit->desc);
        return true;
    }
}
