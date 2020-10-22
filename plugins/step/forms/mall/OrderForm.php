<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\step\forms\mall;

use app\forms\mall\order\BaseOrderForm;
use app\plugins\step\models\StepOrder;

class OrderForm extends BaseOrderForm
{
    protected function getExtra($order)
    {
        $order = StepOrder::findOne(['order_id' => $order['id']]);
        return [
            'currency' => $order->currency ?? ''
        ];
    }
}
