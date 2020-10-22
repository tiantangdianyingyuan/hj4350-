<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;


use app\forms\mall\order\BaseOrderForm;
use app\plugins\integral_mall\models\IntegralMallOrders;

class OrderForm extends BaseOrderForm
{
    protected function getExtra($order)
    {
        $order = IntegralMallOrders::findOne(['order_id' => $order['id']]);
        return [
            'integral_num' => $order->integral_num
        ];
    }
}
