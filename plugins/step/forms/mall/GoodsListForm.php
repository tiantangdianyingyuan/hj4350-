<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\step\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\step\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('stepGoods', 'attr.stepGoods');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['stepGoods'] = isset($goods->stepGoods) ? ArrayHelper::toArray($goods->stepGoods) : [];
        $newItem['attr']['stepGoods'] = isset($goods->attr->stepGoods) ? ArrayHelper::toArray($goods->attr->stepGoods) : [];

        $num_count = 0;
        foreach($goods->attr as $key => $item) {
            $newItem['attr'][$key]['step_currency'] = $item->stepGoods->currency;
            $num_count += $item->stock;
        }
        $newItem['num_count'] = $num_count;

        return $newItem;
    }
}