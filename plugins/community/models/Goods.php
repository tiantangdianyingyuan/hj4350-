<?php

namespace app\plugins\community\models;


/**
 * Class Goods
 * @package app\plugins\community\models
 * @property CommunityGoods $communityGoods 抽奖券码数量
 */
class Goods extends \app\models\Goods
{
    public function getCommunityGoods()
    {
        return $this->hasOne(CommunityGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
