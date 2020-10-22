<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\models;

class Goods extends \app\models\Goods
{
    public function getEgoods()
    {
        return $this->hasOne(ExchangeGoods::className(), ['goods_id' => 'id']);
    }

    public function getLibrary()
    {
        return $this->hasOne(ExchangeLibrary::className(), ['id' => 'library_id'])
            ->viaTable(ExchangeGoods::tableName(), ['goods_id' => 'id']);
    }
}
