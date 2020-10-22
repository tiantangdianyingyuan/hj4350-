<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api\v2;


use app\core\response\ApiCode;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\plugins\pintuan\forms\common\v2\CommonGoods;
use app\plugins\pintuan\forms\common\v2\SettingForm;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use yii\db\Query;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $id;
    public $cat_id;

    public function rules()
    {
        return [
            [['id', 'cat_id'], 'integer'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // 有阶梯团的商品 才在前端展示
        $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])
            ->andWhere(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->groupBy('pintuan_goods_id')
            ->select('pintuan_goods_id');
        $query = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $pintuanGoodsIds,])
            // ->andWhere(['<', 'start_time', mysql_timestamp()])
            ->andWhere([
                'or',
                ['end_time' => '0000-00-00 00:00:00'],
                ['>', 'end_time', mysql_timestamp()]
            ]);
        // 热销拼团商品
        if ($this->cat_id && $this->cat_id == 0) {
            $query->andWhere(['is_sell_well' => 1]);
        }
        $goodsIds = $query->select('goods_id');

        $query = Goods::find()->where([
            'id' => $goodsIds,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->plugin->currentPlugin->getName(),
            'status' => 1,
        ]);
        if ($this->cat_id && $this->cat_id > 0) {
            $goodsWarehouseIds = $this->getCatGoods($this->cat_id);
            $query->andWhere(['goods_warehouse_id' => $goodsWarehouseIds]);
        }

        $list = $query->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->with('goodsWarehouse', 'pintuanGoods')
            ->page($pagination)
            ->all();

        $setting = (new SettingForm())->search();

        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['name'] = $item->name;
            $newItem['cover_pic'] = $item->coverPic;
            $newItem['original_price'] = $item->originalPrice;
            $newItem['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($item)->getAppoint();

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

            // 拼团销量
            $newItem['sales'] = $item->virtual_sales + $item->sales + $goodsSales;

            $goodsList = (new Goods())->getGoodsGroups($item);

            $newItem['price'] = $groupMinPrice;
            $newItem['goods_stock'] = $goodsStock;
            $newItem['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice($goodsList ? $goodsList[0] : $item);
            $newItem['is_level'] = $setting['is_member_price'] ? $item->is_level : 0;
            $newItem['video_url'] = $item->goodsWarehouse->video_url;
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
     * 根据分类筛选商品goods_warehouse_id
     * @param $id
     * @return Query
     */
    public function getCatGoods($id)
    {
        $goodsCatIds = GoodsCats::find()->andWhere([
            'or',
            ['id' => $id],
            ['parent_id' => $id]
        ])
            ->select('id');

        $goodsCatIds = GoodsCats::find()->andWhere([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0,
            'status' => 1,
        ])->andWhere([
            'or',
            ['id' => $goodsCatIds],
            ['parent_id' => $goodsCatIds]
        ])->select('id');

        $goodsWarehouseIds = GoodsCatRelation::find()->where([
            'is_delete' => 0,
            'cat_id' => $goodsCatIds
        ])->select('goods_warehouse_id');

        return $goodsWarehouseIds;
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $newGoods = $form->getAll();

            $goodsList = (new Goods())->getGoodsGroups($goods);

            $groups = [];
            $groupsPriceMax = 0;// 所有阶梯团中最大价格
            $groupsPriceMin = 0;// 所有阶梯团中最小价格
            $goodsStock = 0;
            /** @var Goods $gItem */
            foreach ($goodsList as $gItem) {
                $form->goods = $gItem;
                $ptGoods = $form->getAll(['attr', 'price_min', 'price_max', 'share']);
                // 获取最大分销价
                $newGoods['share'] = max($newGoods['share'], $ptGoods['share']);

                $groupsPriceMax = $groupsPriceMax == 0 ? $ptGoods['price_max'] : max($ptGoods['price_max'], $groupsPriceMax);
                $groupsPriceMin = $groupsPriceMin == 0 ? $ptGoods['price_min'] : min($ptGoods['price_min'], $groupsPriceMin);
                $memberPriceMin = 0;
                $memberPriceMax = 0;

                foreach ($ptGoods['attr'] as $aItem) {
                    $goodsStock += $aItem['stock'];
                    if ($memberPriceMin == 0) {
                        $memberPriceMin = $aItem['price_member'];
                    } else {
                        $memberPriceMin = min($memberPriceMin, $aItem['price_member']);
                    }

                    if ($memberPriceMax == 0) {
                        $memberPriceMax = $aItem['price_member'];
                    } else {
                        $memberPriceMax = max($memberPriceMax, $aItem['price_member']);
                    }
                }

                $groups[] = [
                    'group_id' => $gItem->oneGroups->id,
                    'groups' => $gItem->oneGroups,
                    'attr' => $ptGoods['attr'],
                    'price_min' => $ptGoods['price_min'],
                    'price_max' => $ptGoods['price_max'],
                    'member_price_min' => $memberPriceMin,
                    'member_price_max' => $memberPriceMax,
                ];
            }

            foreach ($goods->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            $newGoods['goods_stock'] = $goodsStock;
            $newGoods['groups'] = $groups;

            // 最大赠送积分
            if (isset($newGoods['goods_marketing_award']['integral']['title']) &&
                $newGoods['goods_marketing_award']['integral']['title'] && $newGoods['give_integral_type'] == 2) {

                $price = bcmul($groupsPriceMax, $newGoods['give_integral'] / 100);
                $newGoods['goods_marketing_award']['integral']['title'] = preg_replace_callback(
                    '/^(\D+)(\d*)(\D+)$/',
                    function ($matches) use ($price) {
                        return $matches[1] . max([$price, $matches[2]]) . $matches[3];
                    },
                    $newGoods['goods_marketing_award']['integral']['title']
                );
            }

            /** @var PintuanGoods $ptGoods */
            $ptGoods = PintuanGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'goods_id' => $goods->id
            ])->one();
            $newGoods['pintuanGoods'] = $ptGoods;

            $setting = (new SettingForm())->search();
            $newGoods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $newGoods['goods_marketing']['limit'] : '';

            // 判断插件分销是否开启
            if ($setting['is_share'] != 1) {
                $newGoods['share'] = 0;
            }
            $newGoods['group_economize_price'] = price_format($goods->originalPrice - $groupsPriceMin);
            $newGoods['level_show'] = $setting['is_member_price'] ? $newGoods['level_show'] : 0;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'detail' => $newGoods,
                    'pintuan_list' => $this->getPintuanList($ptGoods),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    /**
     * 拼团商品详情，正在拼团的列表
     * @param PintuanGoods $ptGoods
     * @return array
     */
    private function getPintuanList($ptGoods)
    {
        /** @var PintuanGoods $pintuanGoods */
        $pintuanGoods = PintuanGoods::find()->where([
            'pintuan_goods_id' => $ptGoods->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ])->all();

        $goodsIds = [$ptGoods->goods_id];
        /** @var PintuanGoods $item */
        foreach ($pintuanGoods as $item) {
            $goodsIds[] = $item->goods_id;
        }

        $list = PintuanOrders::find()->where([
            'status' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $goodsIds,
        ])
            ->with('goods', 'orderRelation.user.userInfo', 'orderRelation.order')
            ->orderBy(['expected_over_time' => SORT_ASC])
            ->limit(10)
            ->all();

        $newList = [];
        /** @var PintuanOrders $item */
        foreach ($list as $item) {
            $newItem = [];
            $newItem['id'] = $item->id;
            $newItem['goods_id'] = $item->goods->id;

            $newItemList = [];// 已支付才算参团成功
            /** @var PintuanOrderRelation $orItem */
            foreach ($item->orderRelation as $orItem) {

                if ($orItem->robot_id == 0 && ($orItem->order->is_pay == 1 || $orItem->order->pay_type == 2)) {
                    if ($orItem->is_parent == 1) {
                        $newItem['nickname'] =$orItem->user->nickname;
                        $newItem['avatar'] =$orItem->user->userInfo->avatar;
                    }
                    $newItemList[] = $orItem;
                }
                if ($orItem->robot_id > 0) {
                    $newItemList[] = $orItem;
                }
            }
            $pintuanTime = strtotime($item->created_at) + $item->pintuan_time * 60 * 60;
            $newItem['surplus_people'] = (int)($item->people_num - count($newItemList));
            $newItem['surplus_time'] = ($pintuanTime - time()) > 0 ? $pintuanTime - time() : 0;
            $newItem['surplus_date_time'] = date('Y-m-d H:i:s', $pintuanTime);
            $newList[] = $newItem;
        }

        return $newList;
    }
}
