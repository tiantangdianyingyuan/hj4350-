<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/18
 * Time: 11:54
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\refund;


use app\models\Model;
use app\models\PaymentOrderUnion;
use app\models\PaymentRefund;

abstract class BaseRefund extends Model
{
    /**
     * @param PaymentRefund $paymentRefund
     * @param PaymentOrderUnion $paymentOrderUnion
     * @return mixed
     */
    abstract public function refund($paymentRefund, $paymentOrderUnion);
}
