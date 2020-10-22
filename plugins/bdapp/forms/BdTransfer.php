<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/11
 * Time: 15:29
 */

namespace app\plugins\bdapp\forms;

use app\core\payment\PaymentException;
use app\forms\common\transfer\BaseTransfer;
use app\plugins\wxapp\Plugin;
use luweiss\Wechat\WechatException;

class BdTransfer extends BaseTransfer
{
    public function transfer($paymentTransfer, $user)
    {
        throw new \Exception('百度用户暂不支持提现功能，请使用其他方式提现~');
    }
}