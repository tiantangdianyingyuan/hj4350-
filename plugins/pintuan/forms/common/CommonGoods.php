<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common;


use app\forms\api\goods\ApiGoods;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\GoodsMemberPrice;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\OrderDetail;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanGoodsMemberPrice;
use app\plugins\pintuan\models\PintuanGoodsShare;
use app\plugins\step\models\GoodsAttr;

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
     * 删除阶梯团信息
     */
    public function destroyPintuanGroups()
    {
        $res = PintuanGoodsGroups::updateAll([
            'is_delete' => 1
        ], [
            'goods_id' => $this->goods->id,
            'is_delete' => 0
        ]);

        $res = PintuanGoodsAttr::updateAll([
            'is_delete' => 1
        ], [
            'goods_id' => $this->goods->id,
            'is_delete' => 0
        ]);

        $res = PintuanGoodsMemberPrice::updateAll([
            'is_delete' => 1
        ], [
            'goods_id' => $this->goods->id,
            'is_delete' => 0
        ]);

        $res = PintuanGoodsShare::updateAll([
            'is_delete' => 1
        ], [
            'goods_id' => $this->goods->id,
            'is_delete' => 0
        ]);
    }

    /**
     * @return self
     */
    public static function getCommon()
    {
        return new self();
    }

    /**
     * @param $goodsId
     * @return array|\yii\db\ActiveRecord|null|PintuanGoods
     */
    public function getPintuanGoods($goodsId)
    {
        $pintuanGoods = PintuanGoods::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->andWhere(['goods_id' => $goodsId])->one();
        return $pintuanGoods;
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
            $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        /* @var PintuanGoods[] $pintuanList */
        $goodsId = Goods::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $pintuanList = PintuanGoods::find()->with(['groups.attr', 'goods.goodsWarehouse'])
            ->where(['goods_id' => $goodsId, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($pintuanList as $pintuan) {
            $newItem = $common->getDiyBack($pintuan->goods);
            $groups = [];
            foreach ($pintuan->groups as $group) {
                $pintuanPrice = array_column($group->attr, 'pintuan_price');
                $groups[] = [
                    'people_num' => $group->people_num,
                    'preferential_price' => $group->preferential_price,
                    'pintuan_price' => $pintuanPrice ? min($pintuanPrice) : 0
                ];
            }
            $newItem = array_merge($newItem, [
                'is_alone_buy' => $pintuan->is_alone_buy,
                'end_time' => $pintuan->end_time,
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
     * @param $goodsIdList
     * @return array|\yii\db\ActiveRecord[]|PintuanGoods[]
     */
    public function getList($goodsIdList)
    {
        $pintuanList = PintuanGoods::find()->with(['groups.attr'])
            ->where(['goods_id' => $goodsIdList, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->all();
        return $pintuanList;
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
            // TODO MYSQL此处查询过多
            $groupGoodsIds = PintuanGoodsGroups::find()->where([
                'is_delete' => 0,
            ])->groupBy('goods_id')->select('goods_id');

            $goodsIds = PintuanGoods::find()->andWhere([
                '>', 'end_time', mysql_timestamp(),
            ])
                ->andWhere(['start_time' =>  '0000-00-00 00:00:00'])
                ->andWhere(['goods_id' => $groupGoodsIds])
                ->select('goods_id');

            $goodsWarehouseId = GoodsWarehouse::find()->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->select('id');

            $list = Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'sign' => (new Plugin())->getName(),
                'status' => 1,
                'id' => $goodsIds,
                'goods_warehouse_id' => $goodsWarehouseId
            ])->with('goodsWarehouse', 'attr.memberPrice', 'groups.attr.memberPrice')->apiPage()->all();
            $newList = [];
            /* @var Goods[] $list */
            foreach ($list as $k => $item) {
                // TODO 可以优化 可改成$item->sales
                $pintuanOrders = PintuanOrders::find()->where([
                    'goods_id' => $item['id'],
                    'status' => 2
                ])->with('orderRelation')->all();
                $pintuanCount = 0;
                /** @var PintuanOrders $order */
                foreach ($pintuanOrders as $order) {
                    $pintuanCount += count($order->orderRelation);
                }

                $groupMinPrice = 0;
                if (isset($item->groups[0])) {
                    /** @var PintuanGoodsGroups $group */
                    foreach ($item->groups[0]->attr as $aItem) {
                        $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $aItem->pintuan_price)
                            : $aItem->pintuan_price;
                    }
                }
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $newList[$k] = $apiGoods->getDetail();
                $newList[$k]['price'] = $groupMinPrice ?: $item->price;
                $newList[$k]['group_count'] = '已团' . ($pintuanCount + $item->virtual_sales) . $item->goodsWarehouse->unit;

                $goodsStock = 0;
                foreach ($item->attr as $aItem) {
                    $goodsStock += $aItem->stock;
                }
                foreach ($item->groups as $gItem) {
                    foreach ($gItem->attr as $aItem) {
                        $goodsStock += $aItem->pintuan_stock;
                    }
                }
                $newList[$k]['goods_stock'] = $goodsStock;
            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
    }

    private $list;

    public function getGoodsMemberPrice($goods)
    {
        $commonGoodsMember = CommonGoodsMember::getCommon();
        $commonGoodsMember->is_level = \Yii::$app->mall->getMallSettingOne('is_member_user_member_price') == 1 ? $goods->is_level : 0;
        $commonGoodsMember->setLevel();
        $commonGoodsMember->priceList = $this->getPriceList($goods, $commonGoodsMember->level);
        return $commonGoodsMember->getGoodsMember();
    }

    /**
     * @param Goods $goods
     * @param $level
     * @return array
     * 获取拼团商品价格列表
     */
    public function getPriceList($goods, $level)
    {
        if (isset($this->list[$goods->id])) {
            return $this->list[$goods->id];
        }
        $list = [];
        if ($level > 0) {
            if ($goods->is_level_alone == 0) {
                $list = array_column($goods->attr, 'price');
                if (!empty($goods->groups)) {
                    foreach ($goods->groups as $group) {
                        foreach ($group->attr as $attr) {
                            array_push($list, $attr->pintuan_price);
                        }
                    }
                }
            } else {
                $list = [];
                foreach ($goods->attr as $attr) {
                    if (!empty($attr->memberPrice)) {
                        foreach ($attr->memberPrice as $memberPrice) {
                            if ($memberPrice->level == $level) {
                                array_push($list, $memberPrice->price);
                                break;
                            }
                        }
                    }
                }
                if (!empty($goods->groups)) {
                    foreach ($goods->groups as $group) {
                        foreach ($group->attr as $attr) {
                            if (!empty($attr->memberPrice)) {
                                foreach ($attr->memberPrice as $memberPrice) {
                                    if ($memberPrice->level == $level) {
                                        array_push($list, $memberPrice->price);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->list[$goods->id] = $list;
        return $list;
    }

    /**
     * @param Goods|\app\models\Goods $goods
     * @return array
     * 商品列表--单个商品额外信息
     */
    public function getGoodsExtra($goods)
    {
        /** @var Goods $ptGoods */
        $ptGoods = Goods::find()->where(['id' => $goods->id])->with('groups.attr')->one();
        $goodsStock = 0;
        if ($ptGoods) {
            foreach ($ptGoods->groups as $group) {
                foreach ($group->attr as $item) {
                    $goodsStock += $item->pintuan_stock;
                }
            }
        }
        foreach ($goods->attr as $item) {
            $goodsStock += $item->stock;
        }
        return [
            'level_price' => $this->getGoodsMemberPrice($goods),
            'goods_stock' => $goodsStock,
        ];
    }
}
