<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\models;


/**
 * Class Goods
 * @package app\plugins\integral_mall\models
 * @property IntegralMallGoods $integralMallGoods
 */
class Goods extends \app\models\Goods
{
    public function getIntegralMallGoods()
    {
        return $this->hasOne(IntegralMallGoods::className(), ['goods_id' => 'id']);
    }
}
