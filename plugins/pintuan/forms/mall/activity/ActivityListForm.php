<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall\activity;


use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;

class ActivityListForm extends Model
{
    public $search;

    public function rules()
    {
        return [
            [['search'], 'string']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $this->search = [];
        }

        $goodsIds = PintuanGoods::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'pintuan_goods_id' => 0,
        ])
            ->andWhere(['!=', 'start_time', '0000-00-00 00:00:00'])
            ->select('goods_id');

        /** @var BaseActiveQuery $query */
        $query = Goods::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->plugin->currentPlugin->getName(),
            'id' => $goodsIds
        ]);

        $query = $this->setKeyword($query);
        $query = $this->setStatus($query);
        $query = $this->setDate($query);

        $list = $query->with('pintuanGoods')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $groupList = $this->getPtGroups($list);

        $newList = [];
        /** @var Goods $goods */
        foreach ($list as $goods) {
            $newItem = [];
            $newItem['id'] = $goods->id;
            $newItem['goods_name'] = $goods->getName();
            $newItem['goods_cover_pic'] = $goods->getCoverPic();
            $newItem['open_date'] = $goods->pintuanGoods->start_time;
            $newItem['end_date'] = $goods->pintuanGoods->end_time;
            $newItem['status_cn'] = $goods->getActivityStatus($goods);
            $goodsStock = 0;
            $newGroups = [];
            /** @var Goods $groupItem */
            foreach ($groupList as $groupItem) {
                if ($groupItem->pintuanGoods->pintuan_goods_id == $goods->pintuanGoods->id) {
                    $minPrice = 0;
                    /** @var GoodsAttr $attrItem */
                    foreach ($groupItem->attr as $attrItem) {
                        $goodsStock += $attrItem->stock;
                        $minPrice = $minPrice == 0 ? $attrItem->price : min($minPrice, $attrItem->price);
                    }
                    $newGroupItem = [];
                    $newGroupItem['people_num'] = $groupItem->oneGroups->people_num;
                    $newGroupItem['price'] = $minPrice;// 最小价
                    $newGroups[$groupItem->oneGroups->people_num] = $newGroupItem;
                }
            }
            ksort($newGroups);
            $newGroups = array_values($newGroups);
            foreach ($goods->attr as $attrItem) {
                $goodsStock += $attrItem->stock;
            }
            $newItem['goods_stock'] = $goodsStock;//单独购买库存 + 阶梯团库存 = 总库存
            $newItem['groups'] = $newGroups;
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

    // 获取列表所有拼团组
    private function getPtGroups($list)
    {
        // 获取拼团组信息
        $ptGoodsIds = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $ptGoodsIds[] = $item->pintuanGoods->id;
        }
        $goodsIds = PintuanGoods::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'pintuan_goods_id' => $ptGoodsIds
        ])->select('goods_id');

        $groupList = Goods::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->plugin->currentPlugin->getName(),
            'id' => $goodsIds
        ])
            ->with('attr', 'pintuanGoods', 'oneGroups')
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $groupList;
    }

    /**
     * 活动日期搜索
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setDate($query)
    {
        // 日期搜索
        $search = $this->search;
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $goodsIds = PintuanGoods::find()->andWhere([
                'or',
                ['between', 'start_time', $search['date_start'], $search['date_end']],
                ['between', 'end_time', $search['date_start'], $search['date_end']],
                ['and',
                    [
                        '<=', 'start_time', $search['date_start']
                    ],
                    [
                        '>=', 'end_time', $search['date_end']
                    ]
                ]
            ])->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->select('goods_id');

            $query->andWhere(['id' => $goodsIds]);
        }

        return $query;
    }

    /**
     * 活动状态搜索
     * @param BaseActiveQuery $query
     * @return mixed
     */
    private function setStatus($query)
    {
        $search = $this->search;
        if (isset($search['status']) && $search['status'] != 1) {
            $today = date('Y-m-d H:i:s');
            $ptQuery = PintuanGoods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            switch ($search['status']) {
                // 未开始
                case 2:
                    $ptQuery->andWhere(['>', 'start_time', $today]);
                    $query->andWhere(['status' => 1]);
                    break;
                // 进行中
                case 3:
                    $ptQuery->andWhere([
                        'or',
                        [
                            'and',
                            ['<=', 'start_time', $today],
                            ['>', 'end_time', $today]
                        ],
                        [
                            'and',
                            ['<=', 'start_time', $today],
                            ['end_time' => '0000-00-00 00:00:00']
                        ]
                    ]);
                    $query->andWhere(['status' => 1]);
                    break;
                // 已结束
                case 4:
//                    $ptQuery->andWhere(['<', 'end_time', $today]);
                    $ptQuery->andWhere("if((`end_time`= '0000-00-00 00:00:00'),(`end_time` != '0000-00-00 00:00:00'),(`end_time` < '$today'))");
                    $query->andWhere(['status' => 1]);
                    break;
                // 下架中
                case 5:
                    $query->andWhere(['status' => 0]);
                    break;
                default:
                    break;
            }
            $goodsIds = $ptQuery->select('goods_id');
            $query->andWhere(['id' => $goodsIds]);
        }
        return $query;
    }

    // 商品名称搜索&&商品类型
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
            $query->andWhere(['id' => $goodsIds]);
        }

        return $query;
    }
}
