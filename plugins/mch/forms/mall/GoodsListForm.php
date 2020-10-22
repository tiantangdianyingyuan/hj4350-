<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('goodsWarehouse.mchCats', 'mallGoods', 'mch.store');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['mchGoods'] = isset($goods->mchGoods) ? ArrayHelper::toArray($goods->mchGoods) : [];
        $newItem['mallGoods'] = isset($goods->mallGoods) ? ArrayHelper::toArray($goods->mallGoods) : [];
        $newItem['mch'] = isset($goods->mch) ? ArrayHelper::toArray($goods->mch) : [];
        $newItem['store'] = isset($goods->mch->store) ? ArrayHelper::toArray($goods->mch->store) : [];

        return $newItem;
    }
}