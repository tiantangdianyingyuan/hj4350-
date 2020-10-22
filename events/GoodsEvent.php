<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\events;


use yii\base\Event;

class GoodsEvent extends Event
{
    public $goods;
    public $diffAttrIds;

    public $isVipCardGoods;
}
