<?php

namespace app\plugins\lottery\models;


/**
 * Class Goods
 * @package app\plugins\lottery\models
 * @property Lottery $lotteryGoods 抽奖券码数量
 */
class Goods extends \app\models\Goods
{
    public function getLotteryGoods()
    {
        return $this->hasOne(Lottery::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
