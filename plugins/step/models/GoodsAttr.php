<?php

namespace app\plugins\step\models;

/**
 * @property StepGoodsAttr $stepGoods
 */

class GoodsAttr extends \app\models\GoodsAttr
{
    public function getStepGoods()
    {
        return $this->hasOne(StepGoodsAttr::className(), ['attr_id' => 'id']);
    }
}
