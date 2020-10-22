<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsCatRelation;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\miaosha\models\Goods;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ActivityListForm extends Model
{
    public $search;

    public function rules()
    {
        return [
            [['search'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var BaseActiveQuery $query */
        $query = MiaoshaActivitys::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        try {
            $this->search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $this->search = [];
        }

        $query = $this->setKeyword($query);
        $query = $this->setStatus($query);
        $query = $this->setDate($query);
        $query = $this->setCat($query);

        $list = $query->with('oneMiaoshaGoods.goods', 'miaoshaGoods.goods.attr')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var MiaoshaActivitys $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $oneGoods = isset($item->oneMiaoshaGoods->goods) ? $item->oneMiaoshaGoods->goods : null;
            $newItem['goods_name'] = isset($oneGoods->name) ? $oneGoods->name : '';
            $newItem['goods_cover_pic'] = isset($oneGoods->coverPic) ? $oneGoods->coverPic : '';
            $newItem['goods_id'] = $oneGoods ? $oneGoods->id : 0;

            $goodsStock = 0;
            /** @var MiaoshaGoods $msGoods */
            foreach ($item->miaoshaGoods as $msGoods) {
                if ($msGoods->goods) {
                    foreach ($msGoods->goods->attr as $goodsAttr) {
                        $goodsStock += $goodsAttr->stock;
                    }
                }
            }
            $newItem['goods_stock'] = $goodsStock;
            $newItem['status_cn'] = $item->getActivityStatus($item);
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setDate($query)
    {
        // 日期搜索
        $search = $this->search;
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $query->andWhere([
                'or',
                ['between', 'open_date', $search['date_start'], $search['date_end']],
                ['and',
                    [
                        '<=', 'open_date', $search['date_start']
                    ],
                    [
                        '>=', 'end_date', $search['date_end']
                    ]
                ]
            ]);
        }

        return $query;
    }

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setStatus($query)
    {
        $search = $this->search;
        if (isset($search['status']) && $search['status'] != 1) {
            $today = date('Y-m-d');
            switch ($search['status']) {
                // 未开始
                case 2:
                    $query->andWhere(['>', 'open_date', $today])->andWhere(['status' => 1]);
                    break;
                // 进行中
                case 3:
                    $query->andWhere([
                        'and',
                        ['<=', 'open_date', $today],
                        ['>', 'end_date', $today],
                        ['status' => 1]
                    ]);

                    break;
                // 已结束
                case 4:
                    $query->andWhere(['<', 'end_date', $today])->andWhere(['status' => 1]);
                    break;
                // 下架中
                case 5:
                    $query->andWhere(['status' => 0]);
                    break;
                default:
                    break;
            }
        }
        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @return mixed
     */
    private function setKeyword($query)
    {
        $search = $this->search;
        $goodsWarehouseCondition = [];
        if (isset($search['keyword']) && $search['keyword']) {
            $goodsWarehouseCondition[] = ['like', 'name', $search['keyword']];
        }
        if (isset($search['type']) && $search['type']) {
            $goodsWarehouseCondition[] = ['type' => $search['type']];
        }
        if (!empty($goodsWarehouseCondition)) {
            array_unshift($goodsWarehouseCondition, 'and');
            $goodsWarehouseIds = GoodsWarehouse::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->andWhere($goodsWarehouseCondition)->select('id');
            $goodsIds = Goods::find()->andWhere([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'goods_warehouse_id' => $goodsWarehouseIds,
                'sign' => \Yii::$app->plugin->currentPlugin->getName(),
            ])->select('id');
            $activityIds = MiaoshaGoods::find()->where([
                'is_delete' => 0,
                'goods_id' => $goodsIds,
                'mall_id' => \Yii::$app->mall->id,
            ])->groupBy('activity_id')->select('activity_id');
            $query->andWhere(['id' => $activityIds]);
        }

        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @return mixed
     */
    private function setCat($query)
    {
        if (isset($this->search['cats']) && $this->search['cats']) {
            $goodsWarehouseIds = GoodsCatRelation::find()->where(['cat_id' => $this->search['cats']])->select('goods_warehouse_id');
            $activityIds = MiaoshaGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'goods_warehouse_id' => $goodsWarehouseIds
            ])
                ->andWhere(['>', 'activity_id', 0])
                ->select('activity_id');

            $query->andWhere(['id' => $activityIds]);
        }

        return $query;
    }
}
