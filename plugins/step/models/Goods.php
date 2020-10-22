<?php

namespace app\plugins\step\models;

class Goods extends \app\models\Goods
{
    public function getStepGoods()
    {
        return $this->hasOne(StepGoods::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
