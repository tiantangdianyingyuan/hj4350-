<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\events;

use yii\base\Event;

class OrderSendEvent extends Event
{
    public $order;
}
