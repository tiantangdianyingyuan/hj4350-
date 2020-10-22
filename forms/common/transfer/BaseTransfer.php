<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/19
 * Time: 11:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\transfer;


use app\models\Model;
use app\models\PaymentTransfer;
use app\models\User;

abstract class BaseTransfer extends Model
{
    /**
     * @param PaymentTransfer $paymentTransfer
     * @param User $user
     * @return mixed
     */
    abstract public function transfer($paymentTransfer, $user);
}
