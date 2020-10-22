<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\miaosha\models\MiaoshaGoods;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\miaosha\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])
            ->with('miaoshaGoods')
            ->groupBy('goods_warehouse_id');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['miaoshaGoods'] = isset($goods->miaoshaGoods) ? ArrayHelper::toArray($goods->miaoshaGoods) : [];
        $count = MiaoshaGoods::find()->where([
            'goods_warehouse_id' => $goods->goods_warehouse_id,
            'mall_id' => $goods->mall_id,
            'is_delete' => 0,
        ])->count();
        $newItem['miaosha_count'] = $count;

        return $newItem;
    }
}