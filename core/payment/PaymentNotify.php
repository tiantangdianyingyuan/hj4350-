<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/11 11:32
 */


namespace app\core\payment;


use yii\base\Component;


abstract class PaymentNotify extends Component
{
    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    abstract public function notify($paymentOrder);
}
