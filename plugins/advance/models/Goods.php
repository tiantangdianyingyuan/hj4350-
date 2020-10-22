<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\models;


use app\models\GoodsCatRelation;

/**
 * Class Goods
 * @package app\plugins\advance\models
 * @property AdvanceGoods $advanceGoods
 * @property GoodsCatRelation $cat
 */
class Goods extends \app\models\Goods
{
    public function getAdvanceGoods()
    {
        return $this->hasOne(AdvanceGoods::className(), ['goods_id' => 'id']);
    }

    public function getCat()
    {
        return $this->hasOne(GoodsCatRelation::className(), ['goods_warehouse_id' => 'goods_warehouse_id']);
    }
}
