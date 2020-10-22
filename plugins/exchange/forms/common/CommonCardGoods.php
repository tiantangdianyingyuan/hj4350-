<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;


use app\forms\common\goods\CommonGoodsList;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\exchange\models\Goods;

class CommonCardGoods extends Model
{
    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        $goodsList = Goods::find()->where([
            'status' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => 'exchange'
        ])
            ->page($pagination)
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->all();

        $common = new CommonGoodsList();
        $newList = [];
        foreach ($goodsList as $goods) {
            $newItem = $common->getDiyBack($goods);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }
}