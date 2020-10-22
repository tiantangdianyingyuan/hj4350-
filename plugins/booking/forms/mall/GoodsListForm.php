<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\booking\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\booking\models\Goods;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\booking\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])
            ->with('bookingGoods');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        /** @var Goods  $goods */
        $newItem = [];
        $newItem['bookingGoods']['id'] = $goods->bookingGoods->id;

        return $newItem;
    }
}