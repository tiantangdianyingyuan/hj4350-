<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common\v2;


use app\forms\api\goods\ApiGoods;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;

class CommonGoods extends Model
{
    public $goods;

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null|Goods
     */
    public static function getGoodsDetail($id)
    {
        $detail = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $id,
            'sign' => ((new Plugin())->getName())
        ])->one();

        return $detail;
    }

    /**
     * @return self
     */
    public static function getCommon()
    {
        return new self();
    }

    /**
     * @param array $array
     * @return array
     * 获取diy商品列表信息
     */
    public function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        /* @var PintuanGoods[] $pintuanList */
        $goodsIds = Goods::find()
            ->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => (new Plugin())->getName()])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');


        // 有阶梯团的商品 才在前端展示
        $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])->andWhere([
            'is_delete' => 0,
            'goods_id' => $goodsIds,
            'mall_id' => \Yii::$app->mall->id
        ])->groupBy('pintuan_goods_id')->select('pintuan_goods_id');

        $goodsIds = $query = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $pintuanGoodsIds,
        ])->andWhere([
                'or',
                ['end_time' => '0000-00-00 00:00:00'],
                ['>', 'end_time', mysql_timestamp()]
            ])->select('goods_id');

        $query = Goods::find()->where([
            'id' => $goodsIds,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ]);

        $list = $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->with('goodsWarehouse', 'pintuanGoods')
            ->page($pagination)
            ->all();


        $common = new CommonGoodsList();
        $newList = [];
        /** @var Goods $goods */
        foreach ($list as $goods) {
            $newItem = $common->getDiyBack($goods);
            $groups = [];
            $goodsIds = PintuanGoods::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'pintuan_goods_id' => $goods->pintuanGoods->id])
                ->select('goods_id');
            $goodsList = Goods::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsIds])->with('attr')
                ->all();

            /** @var Goods $gItem */
            foreach ($goodsList as $gItem) {
                $price = array_column($gItem->attr, 'price');
                $groups[] = [
                    'people_num' => $gItem->oneGroups->people_num,
                    'preferential_price' => $gItem->oneGroups->preferential_price,
                    'pintuan_price' => $price ? min($price) : 0
                ];
            }
            $newItem = array_merge($newItem, [
                'is_alone_buy' => $goods->pintuanGoods->is_alone_buy,
                'end_time' => $goods->pintuanGoods->end_time,
                'groups' => $groups,
            ]);
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '拼团',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            // 有阶梯团的商品 才在前端展示
            $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])->andWhere([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])->groupBy('pintuan_goods_id')->select('pintuan_goods_id');

            $query = PintuanGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $pintuanGoodsIds,
            ])->andWhere([
                    'or',
                    ['end_time' => '0000-00-00 00:00:00'],
                    ['>', 'end_time', mysql_timestamp()]
                ]);
            $goodsIds = $query->select('goods_id');

            $query = Goods::find()->where([
                'id' => $goodsIds,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'sign' => (new Plugin())->getName(),
                'status' => 1,
            ]);

            $list = $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->with('goodsWarehouse', 'pintuanGoods')
                ->apiPage()
                ->all();

            $newList = [];
            /* @var Goods $item */
            foreach ($list as $item) {
                $pintuanOrders = PintuanOrders::find()->where([
                    'goods_id' => $item['id'],
                    'status' => 2
                ])->with('orderRelation.order')->all();
                $pintuanCount = 0;
                /** @var PintuanOrders $order */
                foreach ($pintuanOrders as $order) {
                    /** @var PintuanOrderRelation $orItem */
                    foreach ($order->orderRelation as $orItem) {
                        if ($orItem->robot_id > 0 && $orItem->order->is_pay == 1 && $orItem->order->cancel_status == 0) {
                            $pintuanCount += 1;
                        }
                    }
                }

                $goodsIds = PintuanGoods::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'pintuan_goods_id' => $item->pintuanGoods->id
                ])->select('goods_id');
                $goodsList = Goods::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'id' => $goodsIds
                ])->with('attr')->all();
                $groupMinPrice = 0;// 阶梯团规格最小价
                $goodsStock = 0;// 商品总库存（单独购买 + 阶梯团）
                $goodsSales = 0;
                /** @var Goods $gItem */
                foreach ($goodsList as $gItem) {
                    foreach ($gItem->attr as $aItem) {
                        if ($groupMinPrice == 0) {
                            $groupMinPrice = $aItem->price;
                        } else {
                            $groupMinPrice = min($groupMinPrice, $aItem->price);
                        }
                        $goodsStock += CommonEcard::getCommon()->getEcardStock($aItem->stock, $gItem);
                    }
                    $goodsSales += $gItem->sales;
                }
                foreach ($item->attr as $aItem) {
                    $goodsStock += CommonEcard::getCommon()->getEcardStock($aItem->stock, $item);
                }

                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $newItemGoods = $apiGoods->getDetail();
                $newItemGoods['price'] = $groupMinPrice;
                $newItemGoods['group_count'] = '已团' . ($item->virtual_sales + $item->sales + $goodsSales) . $item->goodsWarehouse->unit;
                $newItemGoods['goods_stock'] = $goodsStock;
                unset($newItemGoods['attr']);
                unset($newItemGoods['attr_groups']);
                $newList[] = $newItemGoods;

            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
    }

    /**
     * @param Goods|\app\models\Goods $goods
     * @return array
     * 商品列表--单个商品额外信息
     */
    public function getGoodsExtra($goods)
    {
        /** @var PintuanGoods $pintuanGoods */
        $pintuanGoods = PintuanGoods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goods->id])
            ->one();
        $goodsIds = PintuanGoods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'pintuan_goods_id' => $pintuanGoods->id])
            ->select('goods_id');
        $goodsList = Goods::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsIds])->with('attr')
            ->all();
        $goodsStock = 0;// 商品总库存（单独购买 + 阶梯团）
        $groupMinPrice = 0;
        /** @var Goods $gItem */
        foreach ($goodsList as $gItem) {
            foreach ($gItem->attr as $aItem) {
                if ($groupMinPrice == 0) {
                    $groupMinPrice = $aItem->price;
                } else {
                    $groupMinPrice = min($groupMinPrice, $aItem->price);
                }
                $goodsStock += $aItem->stock;
            }
        }
        foreach ($goods->attr as $aItem) {
            $goodsStock += $aItem->stock;
        }

        // TODO 拼团商品会员价 按阶梯团算还是单独购买商品算
        return [
//            'level_price' => $this->getGoodsMemberPrice($goods),
            'goods_stock' => $goodsStock,
            'price' => $groupMinPrice,
            'price_content' => '￥' . $groupMinPrice,
        ];
    }
}
