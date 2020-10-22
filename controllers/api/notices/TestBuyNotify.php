<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/11 14:49
 */


namespace app\controllers\api\notices;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;

class TestBuyNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        \Yii::warning('支付结果通知：' . \Yii::$app->serializer->encode($paymentOrder->attributes));
    }
}
