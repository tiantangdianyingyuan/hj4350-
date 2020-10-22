<?php

namespace app\plugins\booking\models;


/**
 * Class Goods
 * @package app\plugins\booking\models
 * @property Goods $bookingGoods
 */
class Goods extends \app\models\Goods
{
    public function getBookingGoods()
    {
        return $this->hasOne(BookingGoods::className(), ['goods_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }
}
