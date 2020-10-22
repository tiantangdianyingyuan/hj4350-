<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\goods;

use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\mch\models\MchGoods;
use yii\db\Query;

abstract class BaseGoodsList extends Model
{
    public $search;
    public $plugin; // 插件标示--选择商品库商品时
    public $ignore_type; // 导出时忽略的商品类型
    public $goodsModel = 'app\models\Goods';

    public function rules()
    {
        return [
            [['search'], 'safe'],
            ['plugin', 'string'],
            ['plugin', 'default', 'value' => '']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }
        try {
            /** @var Goods $model */
            $model = $this->goodsModel;
            $query = $model::find()->alias('g')->where([
                'g.mall_id' => \Yii::$app->mall->id,
                'g.is_delete' => 0,
            ]);

            $goodsWarehouseCondition = [];
            // 部分插件查询商品时，忽略电子卡密商品
            if ($this->plugin && !in_array($this->plugin, CommonEcard::getCommon()->getSupportEcard())) {
                $goodsWarehouseCondition[] = ['!=', 'type', 'ecard'];
            }

            // 忽略查询的商品类型
            if ($this->ignore_type) {
                $goodsWarehouseCondition[] = ['not in', 'type', $this->ignore_type];
            }

            if (isset($search['type']) && $search['type'] && trim($search['type'])) {
                $type = trim($search['type']);
                $goodsWarehouseCondition[] = ['type' => $type];
            }

            if (!empty($goodsWarehouseCondition)) {
                array_unshift($goodsWarehouseCondition, 'and');
                $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                    ->andWhere($goodsWarehouseCondition)->select('id');
                $condition[] = ['g.goods_warehouse_id' => $goodsIds];
            }

            // 关键字搜索
            if (isset($search['keyword']) && $search['keyword'] && trim($search['keyword'])) {
                $keyword = trim($search['keyword']);
                $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                    ->andWhere(['like', 'name', $keyword])->select('id');
                $condition[] = [
                    'or',
                    ['like', 'g.id', $search['keyword']],
                    ['g.goods_warehouse_id' => $goodsIds]
                ];
            }

            if (!empty($condition)) {
                array_unshift($condition, 'and');
                $query->andWhere($condition);
            }
            // 商品排序
            if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
                $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
                if ($search['sort_prop'] == 'mchGoods.sort') {
                    $query->leftJoin(['mg' => MchGoods::tableName()], 'mg.goods_id=g.id');
                    $query->orderBy(['mg.sort' => $sortType]);
                } else {
                    $query->orderBy(['g.' . $search['sort_prop'] => $sortType]);
                }
            } else {
                $query->orderBy(['g.created_at' => SORT_DESC]);
            }
            // 状态搜索
            $query = $this->setStatus($query, $search);

            // 分类搜索
            if (isset($search['cats']) && $search['cats']) {
                $query = $this->addCatWhere($search['cats'], $query);
            }

            // 日期搜索
            if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
                $query->andWhere(['>=', 'g.created_at', $search['date_start']]);
                $query->andWhere(['<=', 'g.created_at', $search['date_end']]);
            }

            $query = $this->setQuery($query);
            if (\Yii::$app->request->post('flag') == 'EXPORT') {
                return $query;
            }

            $newQuery = clone $query;
            $goodsCount = $newQuery->count();
            $list = $query->with('goodsWarehouse.cats', 'attr')->page($pagination)->all();
            $newList = $this->handleData($list);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination,
                    'goods_count' => $goodsCount,
                    'hide_function' => \Yii::$app->role->getHideFunction(),
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    /**
     * @param $catIds
     * @param Query $query
     * @return mixed
     */
    private function addCatWhere($catIds, $query)
    {
        if (!$catIds) {
            return $query;
        }
        $cat = GoodsCats::find()->select('id')
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'status' => 1,
            ])
            ->andWhere([
                'OR',
                ['parent_id' => GoodsCats::find()->where([
                    'parent_id' => $catIds,
                ])->select('id')],
                ['parent_id' => $catIds],
                ['id' => $catIds],
            ]);
        $goodsCatRelation = GoodsCatRelation::find()->select('goods_warehouse_id')
            ->where(['is_delete' => 0])->andWhere(['in', 'cat_id', $cat]);
        $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->andWhere(['id' => $goodsCatRelation])->select('id');
        $query->andWhere(['g.goods_warehouse_id' => $goodsWarehouseId]);

        return $query;
    }

    /**
     * 提供额外参数
     * @param $query
     * @return BaseActiveQuery mixed
     */
    protected function setQuery($query)
    {
        return $query;
    }

    /**
     * 处理商品列表数据
     * @param $list
     * @return array
     */
    protected function handleData($list)
    {
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['mch_id'] = $item->mch_id;
            $newItem['goods_warehouse_id'] = $item->goods_warehouse_id;
            $newItem['status'] = $item->status;
            $newItem['virtual_sales'] = $item->virtual_sales;
            $newItem['created_at'] = $item->created_at;
            $newItem['sign'] = $item->sign;
            $newItem['sort'] = $item->sort;
            $newItem['goodsWarehouse'] = [];
            $newItem['goodsWarehouse']['id'] = $item->goodsWarehouse->id;
            $newItem['goodsWarehouse']['name'] = $item->goodsWarehouse->name;
            $newItem['goodsWarehouse']['cover_pic'] = $item->goodsWarehouse->cover_pic;
            $newItem['goodsWarehouse']['original_price'] = $item->goodsWarehouse->original_price;
            $newItem['goodsWarehouse']['unit'] = $item->goodsWarehouse->unit;
            $newItem['goodsWarehouse']['type'] = $item->goodsWarehouse->type;
            $newCats = [];
            if ($item->goodsWarehouse && $item->goodsWarehouse->cats) {
                /** @var GoodsCats $cat */
                foreach ($item->goodsWarehouse->cats as $cat) {
                    $newCatItem = [];
                    $newCatItem['id'] = $cat->id;
                    $newCatItem['name'] = $cat->name;
                    $newCats[] = $newCatItem;
                }
            }
            $newItem['cats'] = $newCats;
            $newItem['name'] = $item->goodsWarehouse->name;
            $goodsStock = 0;
            $goodsPrice = 0;
            foreach ($item->attr as $aItem) {
                $goodsStock += $aItem->stock;
                if ($goodsPrice == 0) {
                    $goodsPrice = $aItem->price;
                } else {
                    $goodsPrice = min($goodsPrice, $aItem->price);
                }
            }
            $newItem['price'] = $goodsPrice;
            $newItem['goods_stock'] = $goodsStock;
            $newItem['sales'] = $item->sales;
            $newItem = array_merge($newItem, $this->handleGoodsData($item));
            $newItem['confine_count'] = $item->confine_count;
            $newList[] = $newItem;

        }

        return $newList;
    }

    /**
     * 如果只有小部分参数不同，可重写此接口
     * @param Goods $goods
     * @return array
     */
    protected function handleGoodsData($goods)
    {
        return [];
    }

    /**
     * @param BaseActiveQuery $query
     * @param $search
     * @return mixed
     * 设置status的条件筛选
     */
    protected function setStatus($query, $search)
    {
        if (isset($search['status']) && $search['status'] != -1) {
            if ($search['status'] != '' && ($search['status'] == 0 || $search['status'] == 1)) {
                // 上下架状态
                $query->andWhere(['status' => $search['status']]);
            } elseif ($search['status'] == 2) {
                // 售罄
                $query->andWhere(['goods_stock' => 0]);
            }
        }
        return $query;
    }

    /**
     * 额外需要展示的数据
     * @param Goods $goods
     * @return array
     */
    protected function extraItemData($goods)
    {
        return [];
    }
}
