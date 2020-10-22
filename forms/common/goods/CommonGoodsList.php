<?php

namespace app\forms\common\goods;

use app\core\Pagination;
use app\forms\api\goods\ApiGoods;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\FullReduceActivity;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\mch\models\MchGoods;
use yii\helpers\ArrayHelper;

/**
 * @property Goods $goods
 * @property BaseActiveQuery $query
 * @property Pagination $pagination
 */
class CommonGoodsList extends Model
{
    public $query;
    public $model;
    public $list;
    public $pagination;
    public $limit;
    public $page;
    public $is_array;
    public $goods_id;

    public $cat_id;
    public $group_by_name = 'id';
    public $keyword;
    public $sort;
    public $is_sell_well;
    public $is_quick_shop;
    public $sort_type;
    public $sort_prop;
    public $is_negotiable;
    public $sign = '';
    public $signWhere = [];
    public $mch_id = 0;
    public $status;
    public $relations = [];
    public $goodsWarehouseId;
    public $is_sold_out;
    public $mch_status = -1;
    public $exceptSelf; // 去除掉的商品id

    public $goods_warehouse_ids;

    public $is_sales;
    //关联关系
    public $is_cart;
    public $isSignCondition;

    //添加时间筛选
    public $date_start;
    public $date_end;

    // 去除卡密商品
    public $is_del_ecard;

    //筛选满减活动商品
    public $is_full_reduce;

    public function rules()
    {
        return [
            [['limit', 'is_negotiable', 'status', 'sort', 'sort_type', 'is_sell_well',
                'is_quick_shop', 'mch_id', 'is_cart', 'is_sold_out', 'mch_status', 'is_sales'], 'integer'],
            [['limit'], 'default', 'value' => 10],
            [['keyword', 'goods_warehouse_ids', 'date_start', 'date_end'], 'string'],
            [['is_array', 'isSignCondition'], 'default', 'value' => 0],
            [['page', 'sort'], 'default', 'value' => 1],
            [['model'], 'default', 'value' => 'app\models\Goods'],
            [['relations', 'cat_id', 'goods_id', 'exceptSelf', 'goodsWarehouseId'], 'safe'],
            [['sign', 'sort_prop', 'signWhere'], 'trim'],
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    private function getMethod($key)
    {
        $array = [
            'is_quick_shop' => 'quickShopWhere',
            'is_sell_well' => 'sellWellWhere',
            'is_negotiable' => 'negotiableWhere',
            'keyword' => 'keywordWhere',
            'sort' => 'sortWhere',
            'status' => 'statusWhere',
            'cat_id' => 'setCatWhere',
            'is_sold_out' => 'setSoleOutWhere',
            'goodsWarehouseId' => 'setGoodsWarehouseIdWhere',
            'is_cart' => 'setWithCart',
            'goods_id' => 'setGoodsIdWhere',
            'is_sales' => 'setSalesSelect',
            'exceptSelf' => 'setExceptSelfWhere',
            'isSignCondition' => 'setSignCondition',
            'is_del_ecard' => 'setIsDelEcard',
            'is_full_reduce' => 'setIsFullReduceGoods',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    //持续改进
    public function getQuery()
    {
        /** @var Goods $model */
        $model = $this->model;
        $this->query = $model::find()->alias('g')->where([
            'g.mall_id' => \Yii::$app->mall->id,
            'g.is_delete' => 0,
        ])->select('g.*');
        if ($this->sign) {
            $this->query->andWhere(['g.sign' => $this->sign]);
        } else {
            $this->query->andWhere(['g.sign' => ['']]);
        }

        // 多商户
        if ($this->mch_id > 0) {
            $this->query->andWhere(['g.mch_id' => $this->mch_id]);
        } elseif ($this->mch_id == -1) {
            $this->query->andWhere(['>', 'g.mch_id', 0]);
        }
        if ($this->mch_status >= 0) {
            $this->query->joinWith(['mchGoods AS mg' => function ($query) {
                $query->andWhere(['mg.status' => $this->mch_status]);
            }]);
        }

        //分类查询
        if ($this->goods_warehouse_ids) {
            $this->query->andWhere('g.goods_warehouse_id in (' . $this->goods_warehouse_ids . ')');
        }

        // 日期搜索
        if (isset($this->date_start) && $this->date_start && isset($this->date_end) && $this->date_end) {
            $this->query->andWhere(['>=', 'g.created_at', $this->date_start]);
            $this->query->andWhere(['<=', 'g.created_at', $this->date_end]);
        }

        foreach ($this->attributes as $key => $value) {
            $method = $this->getMethod($key);
            if ($method && method_exists($this, $method) && $value !== null) {
                $this->$method();
            }
        }
        $this->setWith();
    }

    private function setCatWhere()
    {
        if (empty($this->cat_id)) {
            return;
        }
        $catSecond = GoodsCats::find()->select('id')->andWhere([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'parent_id' => $this->cat_id,
        ]);

        $cat = GoodsCats::find()->select('id')->andWhere([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
        ])->andWhere([
            'OR',
            ['id' => $this->cat_id],
            ['in', 'parent_id', $catSecond],
            ['in', 'id', $catSecond],
        ]);

        $goodsWarehouseId = GoodsCatRelation::find()
            ->select('goods_warehouse_id')
            ->where(['is_delete' => 0])
            ->andWhere(['in', 'cat_id', $cat])->column();
        $this->query->andWhere(['goods_warehouse_id' => $goodsWarehouseId]);
    }

    private function setWithCart()
    {
        $this->query->with(['cart' => function ($query) {
            $query->where([
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id,
            ]);
        }]);
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->getQuery();
        return $this->query->page($this->pagination, $this->limit, $this->page)
            ->groupBy($this->group_by_name)
            ->asArray($this->is_array)
            ->all();
    }

    private function setSalesSelect()
    {
        // TODO 销量没有去除 已取消订单等...
        $this->query->addSelect(["total_sales" => "`g`.`sales` + `g`.`virtual_sales`"]);
    }

    private function setWith()
    {
        if (count($this->relations) > 0) {
            $this->query->with($this->relations);
        }
    }

    private function keywordWhere()
    {
        $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
            ->keyword($this->keyword !== '', ['LIKE', 'name', $this->keyword])->select('id');
        $this->query->andWhere([
            'or',
            ['g.goods_warehouse_id' => $goodsIds],
            ['g.id' => $this->keyword],
        ]);
    }

    private function quickShopWhere()
    {
        $this->query->innerjoinWith(['mallGoods' => function ($query) {
            $query->where(['is_quick_shop' => 1]);
        }]);
    }

    private function sellWellWhere()
    {
        $this->query->innerjoinWith(['mallGoods' => function ($query) {
            $query->where(['is_sell_well' => 1]);
        }]);
    }

    private function negotiableWhere()
    {
        $this->query->innerjoinWith(['mallGoods' => function ($query) {
            $query->where(['is_negotiable' => $this->is_negotiable]);
        }]);
    }

    private function statusWhere()
    {
        $this->query->andWhere(['g.status' => $this->status]);
    }

    private function setSoleOutWhere()
    {
        $this->query->andWhere(['g.goods_stock' => 0]);
    }

    private function setGoodsIdWhere()
    {
        if (is_array($this->goods_id) && !count($this->goods_id) > 0) {
            return false;
        }
        $this->query->andWhere(['g.id' => $this->goods_id]);
    }

    private function sortWhere()
    {
        $orderBy = [];
        switch ($this->sort) {
            // 默认
            case 1:
                if ($this->mch_id > 0) {
                    $this->query->joinWith(['mchGoods AS mg']);
                    $orderBy['mg.sort'] = SORT_ASC;
                }
                $orderBy['g.sort'] = SORT_ASC;
                $orderBy['g.created_at'] = SORT_DESC;
                break;
            // 添加时间排序
            case 2:
                $orderBy['g.created_at'] = SORT_DESC;
                break;
            case 3:
                //面议靠前
                if (in_array('mallGoods', $this->relations)) {
                    $this->query->joinWith(['mallGoods AS mgs']);
                    $orderBy['mgs.is_negotiable'] = SORT_ASC;
                }

                $price = GoodsWarehouse::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ])->andWhere('id=g.goods_warehouse_id')->select('price');
                $this->query->addSelect(['newPrice' => $price]);
                if ($this->sort_type == 1) {
                    // 价格升序
                    $orderBy['newPrice'] = SORT_ASC;
                } else {
                    // 价格降序
                    $orderBy['newPrice'] = SORT_DESC;
                }
                break;
            // 销量
            case 4:
                //面议靠前
                if (in_array('mallGoods', $this->relations)) {
                    $this->query->joinWith(['mallGoods AS mgs']);
                    $orderBy['mgs.is_negotiable'] = SORT_ASC;
                }

                $orderBy['total_sales'] = SORT_DESC;
                $orderBy['g.id'] = SORT_DESC;
                break;
            case 5:
                $orderBy['g.updated_at'] = SORT_DESC;
                break;
            // 自定义 列名排序
            case 6:
                $sortType = $this->sort_type ? SORT_ASC : SORT_DESC;
                if ($this->sort_prop == 'mchGoods.sort') {
                    $this->query->leftJoin(['mg' => MchGoods::tableName()], 'mg.goods_id=g.id');
                    $orderBy['g.sort'] = $sortType;
                } else {
                    $orderBy['g.' . $this->sort_prop] = $sortType;
                }
                break;
            default:
        }

        $this->query->orderBy($orderBy);
    }

    private function getGoodsWarehouse()
    {
        $goodsWarehouse = GoodsWarehouse::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        return $goodsWarehouse;
    }

    private function setExceptSelfWhere()
    {
        if ($this->exceptSelf) {
            if (is_array($this->exceptSelf)) {
                $this->query->andWhere(['not in', 'g.id', $this->exceptSelf]);
            } else {
                $this->query->andWhere(['!=', 'g.id', $this->exceptSelf]);
            }
        }
    }

    /**
     * @param null|Goods[] $list
     * @return array
     * 小程序前端
     */
    public function getList($list = null)
    {
        if (!$list) {
            $this->is_array = false;
            $list = $this->search();
        }

        $newList = [];
        /* @var Goods[] $list */
        foreach ($list as $item) {
            $newList[] = $this->getGoodsData($item);
        }
        return $newList;
    }

    public function setGoodsWarehouseIdWhere()
    {
        $this->query->andWhere(['goods_warehouse_id' => $this->goodsWarehouseId]);
    }

    /**
     * @param Goods $goods
     * @return array
     * diy后台获取商品数据公共部分
     */
    public function getDiyBack($goods)
    {
        if (!$goods instanceof Goods) {
            return [];
        }

        $minPrice = 0;
        foreach ($goods->attr as $key => $value) {
            $minPrice = $minPrice == 0 ? $value->price : min($minPrice, $value->price);
        }

        $newItem = [
            'name' => $goods->goodsWarehouse->name,
            'cover_pic' => $goods->goodsWarehouse->cover_pic,
            'original_price' => $goods->goodsWarehouse->original_price,
            'price' => $minPrice,
            'id' => $goods->id,
        ];
        return $newItem;
    }

    public function setSignCondition()
    {
        if (!$this->isSignCondition) {
            return $this;
        }
        if (!$this->sign) {
            return $this;
        }
        try {
            $goodsId = \Yii::$app->plugin->getPlugin($this->sign)->getSignCondition($this->signWhere);
            if (!$goodsId) {
                return $this;
            }
            $this->query->andWhere(['g.id' => $goodsId]);
            return $this;
        } catch (\Exception $exception) {
            return $this;
        }
    }

    /**
     * @param Goods $goods
     * @return string
     */
    public function getPageUrl($goods)
    {
        try {
            if ($goods->mch_id) {
                $plugins = \Yii::$app->plugin->getPlugin('mch');
            } elseif ($goods->sign) {
                $plugins = \Yii::$app->plugin->getPlugin($goods->sign);
            } else {
                throw new \Exception('商城商品');
            }
            if (!method_exists($plugins, 'getGoodsUrl')) {
                throw new \Exception('不存在getGoodsUrl方法');
            }
            $pageUrl = $plugins->getGoodsUrl(['id' => $goods->id, 'mch_id' => $goods->mch_id]);
        } catch (\Exception $exception) {
            $pageUrl = '/pages/goods/goods?id=' . $goods->id;
        }
        return $pageUrl;
    }

    /**
     * @param Goods $goods
     * @return array
     * 小程序端商品列表获取的数据
     */
    public function getGoodsData($goods)
    {
        //small冲突
        $apiGoods = ApiGoods::getCommon();
        $apiGoods->tempGoodsDetail = null;
        $apiGoods->goods = $goods;
        $apiGoods->hasMember = true;
        return $apiGoods->getDetail();
    }

    /**
     * 设置规格
     * @param Goods $goods
     * @param bool $need
     * @return array
     */
    public function setAttr($goods, $need = true)
    {
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = $goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($goods->attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = isset($attrList[$item['sign_id']]) ? $attrList[$item['sign_id']] : [];
            $newItem['price_member'] = 0;
            if ($need) {
                $newItem['member_price_list'] = $item->memberPrice;
            }
            $newAttr[] = $newItem;
        }
        return $newAttr;
    }

    public function setIsDelEcard()
    {
        if ($this->is_del_ecard) {
            $goodsWarehouseId = GoodsWarehouse::find()->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
            ])->andWhere(['!=', 'type', 'ecard'])
                ->select('id');
            $this->query->andWhere(['goods_warehouse_id' => $goodsWarehouseId]);
        }
        return $this;
    }

    public function setIsFullReduceGoods()
    {
        if ($this->is_full_reduce) {
            /**@var FullReduceActivity $activity**/
            $activity = FullReduceActivity::getNowActivity();
            if (!$activity) {
                $this->query->andWhere(['goods_warehouse_id' => []]);
                return $this;
            }
            if ($activity->appoint_type == 1) {
                return $this;
            }
            if ($activity->appoint_type == 2) {
                $this->query->andWhere(['mch_id' => 0]);
                return $this;
            } elseif ($activity->appoint_type == 3) {
                $appointGoods = \Yii::$app->serializer->decode($activity->appoint_goods);
                $this->query->andWhere(['goods_warehouse_id' => $appointGoods]);
                return $this;
            } elseif ($activity->appoint_type == 4) {
                $appointGoods = \Yii::$app->serializer->decode($activity->noappoint_goods);
                $this->query->andWhere(['not in', 'goods_warehouse_id', $appointGoods]);
                return $this;
            }
        }
        return $this;
    }
}
