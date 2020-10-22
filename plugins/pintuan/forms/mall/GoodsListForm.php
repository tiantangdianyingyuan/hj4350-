<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\pintuan\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('pintuanGoods', 'groups.attr');

        return $query;
    }

    /**
     * @param Goods $goods
     * @return array
     */
    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['pintuanGoods'] = isset($goods->pintuanGoods) ? ArrayHelper::toArray($goods->pintuanGoods) : [];
        $newItem['groups'] = isset($goods->groups) ? ArrayHelper::toArray($goods->groups) : [];
        $newItem['name'] = $goods->goodsWarehouse ? $goods->goodsWarehouse->name : '';
        $newItem['cover_pic'] = $goods->goodsWarehouse ? $goods->goodsWarehouse->cover_pic : '';
        $newItem['num_count'] = 0;
        $newItem['status'] = $goods->status;
        $newItem['is_sell_well'] = $goods->pintuanGoods ? $goods->pintuanGoods->is_sell_well : 0;


        $goodsIds = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'pintuan_goods_id' => $goods->pintuanGoods->id
        ])->select('goods_id');
        $goodsList = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $goodsIds
        ])->with('attr')->all();
        $goodsStock = 0;// 商品总库存（单独购买 + 阶梯团）
        /** @var Goods $gItem */
        foreach ($goodsList as $gItem) {
            foreach ($gItem->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
        }
        foreach ($goods->attr as $attr) {
            $newItem['num_count'] += $attr->stock;
        }

        return $newItem;
    }
}