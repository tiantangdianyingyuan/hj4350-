<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\models;


class Order extends \app\models\Order
{
    public function getIntegralOrder()
    {
        return $this->hasOne(IntegralMallOrders::className(), ['order_id' => 'id']);
    }
}
