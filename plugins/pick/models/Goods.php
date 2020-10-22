<?php

namespace app\plugins\pick\models;


/**
 * Class Goods
 * @package app\plugins\lottery\models
 * @property PickGoods $pickGoods 抽奖券码数量
 */
class Goods extends \app\models\Goods
{
    public function getPickGoods()
    {
        return $this->hasOne(PickGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
