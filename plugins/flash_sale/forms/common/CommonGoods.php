<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/30
 * Time: 17:25
 */

namespace app\plugins\flash_sale\forms\common;

use app\forms\common\ecard\CommonEcard;
use app\forms\common\goods\CommonGoodsList;
use app\helpers\PluginHelper;
use app\models\GoodsWarehouse;
use app\plugins\flash_sale\models\FlashSaleActivity;
use app\plugins\flash_sale\models\FlashSaleGoods;
use app\plugins\flash_sale\models\Goods;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

class CommonGoods
{
    /**
     * 商品列表
     * @param string $keyword
     * @param string $goods_id
     * @param int $type
     * @return array
     */
    public static function getList($keyword = '', $goods_id = '', $type = 1)
    {
        $activity = FlashSaleActivity::find()->where(
            [
                'mall_id' => Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 1
            ]
        )->andWhere(['<=', 'start_at', date('Y-m-d H:i:s')])
            ->andWhere(['>=', 'end_at', date('Y-m-d H:i:s')])
            ->one();

        $nextActivity = FlashSaleActivity::find()->where(
            [
                'mall_id' => Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 1
            ]
        )->andWhere(['>=', 'start_at', date('Y-m-d H:i:s')])
            ->orderBy(['start_at' => SORT_ASC])
            ->limit(1)
            ->one();

        $form = new CommonGoodsList();
        $form->model = 'app\plugins\flash_sale\models\Goods';
        $form->sign = 'flash_sale';
        $form->relations = ['goodsWarehouse' => function ($query) {
            $query->select('cost_price,cover_pic,ecard_id,id,name,original_price,pic_url,type,unit,video_url');
        }, 'attr.attr', 'flashSaleGoods'];
        $form->status = 1;
        $form->keyword = $keyword;
        $form->getQuery();
        $query = $form->query;
        if ($goods_id) {
            $query->andWhere(['g.id' => $goods_id]);
        }
        if ($type == 1) {
            $activityId = $activity->id ?? 0;
        } elseif ($type == 2) {
            $activityId = $nextActivity->id ?? 0;
        } elseif ($type == 3) {
            $activityId = [$activity->id ?? 0, $nextActivity->id ?? 0];
        } else {
            $activityId = $activity->id ?? $nextActivity->id ?? 0;
        }
        $query->leftJoin(['fsg' => FlashSaleGoods::tableName()], 'fsg.goods_id = g.id')
            ->andWhere(['fsg.activity_id' => $activityId, 'fsg.is_delete' => 0])
            ->addSelect('fsg.activity_id');

        $pagination = null;
        $list = $query->orderBy('sort ASC')->page($pagination, 10)->all();

        $setting = (new CommonSetting())->search();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['goodsWarehouse'] = ArrayHelper::toArray($item->goodsWarehouse);
            $newItem['flashSaleGoods'] = ArrayHelper::toArray($item->flashSaleGoods);
            $newItem['attr'] = ArrayHelper::toArray($item->attr);
            foreach ($item->attr as $key => $value) {
                if ($value->attr->type == 2 && $value->attr->cut > $item->attr[$key]->price) {
                    continue 2;
                }
                if ($value->attr->type == 1) {
                    $discount = (1 - $value->attr->discount / 10) * $item->attr[$key]->price;
                    $price = $value->price;
                    $price -= min($discount, $price);
                    $newItem['attr'][$key]['price'] = price_format($price);
                } else {
                    $discount = $value->attr->cut;
                    $price = $value->price;
                    $price -= min($discount, $price);
                    $newItem['attr'][$key]['price'] = price_format($price);
                }
                $newItem['attr'][$key]['cut'] = $value->attr->cut;
                $newItem['attr'][$key]['discount'] = $value->attr->discount;
                $newItem['attr'][$key]['type'] = $value->attr->type;
            }
            $attrList = (new Goods())->resetAttr($item['attr_groups']);
            $newItem['goods_stock'] = 0;
            list($discountType, $minDiscount, $minPrice) = self::getMinDiscount($item);
            $goodsData = $form->getGoodsData($item);
            $newAttrList = array_column($goodsData['attr'], null, 'id');
            foreach ($item->attr as $key => $value) {
                $value1 = $newAttrList[$value['id']];
                $newItem['attr'][$key]['stock'] = CommonEcard::getCommon()->getEcardStock($value->stock, $item);
                if ($discountType == 1) {
                    $discount = (1 - $minDiscount / 10) * $value1['price_member'];
                    $value1['price_member'] -= min($discount, $value1['price_member']);
                    $value1['price_member'] = price_format($value1['price_member']);
                    $newItem['attr'][$key]['price_member'] = price_format($value1['price_member']);
                } else {
                    $discount = $minDiscount;
                    $value1['price_member'] -= min($discount, $value1['price_member']);
                    $newItem['attr'][$key]['price_member'] = price_format($value1['price_member']);
                }
                $newItem['goods_stock'] += $value['stock'];
                $newItem['attr'][$key]['attr_list'] = $attrList[$value['sign_id']];
            }
            $count = intval($item['sales']) + intval($item['virtual_sales']);
            if ($type == 1) {
                $sales = $item['sales'] + $item['virtual_sales'];
            } else {
                $sales = 0;
                $count = 0;
            }

            $newItem['price'] = $minPrice;
            $newItem['level_price'] = $goodsData['level_price'] ?? -1;
            if ($discountType == 1) {
                $discount = (1 - $minDiscount / 10) * $minPrice;
                if (isset($goodsData['level_price']) && $goodsData['level_price'] != -1) {
                    $newItem['level_price'] = $goodsData['level_price'];
                    $discountLevel = (1 - $minDiscount / 10) * $goodsData['level_price'];
                    $newItem['level_price'] -= min($discountLevel, $goodsData['level_price']);
                    $newItem['level_price'] = price_format($newItem['level_price'], 'string', 2);
                }
            } else {
                $discount = $minDiscount;
                if (isset($goodsData['level_price']) && $goodsData['level_price'] != -1) {
                    $newItem['level_price'] = $goodsData['level_price'];
                    $newItem['level_price'] -= min($discount, $goodsData['level_price']);
                    $newItem['level_price'] = price_format($newItem['level_price'], 'string', 2);
                }
            }
            $newItem['price'] -= min($discount, $newItem['price']);
            $newItem['price'] = price_format($newItem['price'], 'string', 2);
            if ($newItem['price'] > 0) {
                $newItem['price_content'] = '￥' . price_format($newItem['price']);
            } else {
                $newItem['price_content'] = '免费';
            }
            $newItem['sales'] = '已抢购' . $sales . $item['goodsWarehouse']['unit'];
            $newItem['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $newItem['name'] = $item['goodsWarehouse']['name'];
            $newItem['original_price'] = $item['goodsWarehouse']['original_price'];
            $newItem['page_url'] = '/plugins/flash_sale/goods/goods?id=' . $item['id'];
            $trueStock = CommonEcard::getCommon()->getEcardStock($item['goods_stock'], $item);
            $newItem['percentage'] = self::getPercentage($count, $trueStock);
            $newItem['min_discount'] = $minDiscount;
            $newItem['discount_type'] = $discountType;
            $newItem['type'] = $item['goodsWarehouse']['type'];
            $newItem['attr_groups'] = $goodsData['attr_groups'];
            $newItem['goods_stock'] = $goodsData['goods_stock'];
            $newItem['goods_num'] = $goodsData['goods_stock'];
            $newItem['level_show'] = $setting['is_member_price'] == 1 ? $goodsData['level_show'] : 0;
            $newItem['is_level'] = $setting['is_member_price'] ?? 0;
            isset($goodsData['vip_card_appoint']) && $newItem['vip_card_appoint'] = $goodsData['vip_card_appoint'];
            $newList[] = $newItem;
        }

        return [
            'list' => $newList,
            'activity' => $activity,
            'next_activity' => $nextActivity,
            'pagination' => $pagination
        ];
    }

    public static function getFlashSaleGoods($goodsWarehouseId)
    {
        $activity = FlashSaleActivity::find()
            ->select(['fsa.*', 'fsg.goods_id'])
            ->alias('fsa')
            ->where(
                [
                    'fsa.mall_id' => Yii::$app->mall->id,
                    'fsa.is_delete' => 0,
                    'fsa.status' => 1,
                ]
            )
            ->andWhere(
                [
                    'or',
                    [
                        'and',
                        ['<=', 'fsa.start_at', mysql_timestamp()],
                        ['>=', 'fsa.end_at', mysql_timestamp()],
                    ],
                    [
                        'and',
                        ['fsa.notice' => 1],
                        ['>=', 'fsa.end_at', mysql_timestamp()],
                    ],
                    [
                        'and',
                        ['fsa.notice' => 2],
                        ['<=', 'DATE_SUB(fsa.start_at, interval fsa.notice_hours hour)', mysql_timestamp()],
                        ['>', 'fsa.start_at', mysql_timestamp()],
                    ]
                ]
            )
            ->innerJoin(['fsg' => flashSaleGoods::tableName()], 'fsg.activity_id = fsa.id')
            ->innerJoin(['g' => Goods::tableName()], 'g.id = fsg.goods_id')
            ->innerJoin(['gw' => GoodsWarehouse::tableName()], "gw.id = g.goods_warehouse_id")
            ->andWhere(
                [
                    'gw.id' => $goodsWarehouseId,
                    'gw.is_delete' => 0,
                    'g.is_delete' => 0,
                    'fsg.is_delete' => 0
                ]
            )
            ->orderBy(['fsa.start_at' => SORT_ASC])
            ->asArray()
            ->all();

        try {
            $iconUrl = PluginHelper::getPluginBaseAssetsUrl('flash_sale') . '/img';
        } catch (Exception $exception) {
            $iconUrl = '';
        }

        $newActivity = null;
        $newItem = null;
        foreach ($activity as $item) {
            if (CommonActivity::timeSlot($item) == 2) {
                $newActivity = $item;
            }
        }

        if ($activity) {
            $newActivity = $activity[0];
        }

        if ($newActivity) {
            $flashGoods = Goods::find()->where(
                [
                    'id' => $newActivity['goods_id'],
                    'mall_id' => Yii::$app->mall->id,
                    'is_delete' => 0,
                ]
            )->with(['attr.attr', 'flashSaleGoods'])->one();
            list($discountType, $minDiscount) = CommonGoods::getMinDiscount($flashGoods);
            $newItem['cover'] = $iconUrl . '/flash-sale-goods.png';
            $newItem['discount_type'] = $discountType;
            $newItem['min_discount'] = $minDiscount;
            $newItem['start_at'] = date('Y.m.d', strtotime($newActivity['start_at']));
            $newItem['end_at'] = date('Y.m.d', strtotime($newActivity['end_at']));
            $newItem['time_status'] = CommonActivity::timeSlot($newActivity);
            $newItem['url'] = '/plugins/flash_sale/goods/goods?id=' . $newActivity['goods_id'];
        }
        return $newItem;
    }

    public static function getDiyGoods($array)
    {
        $goodsWarehouseId = null;
        if (isset($array['keyword']) && $array['keyword']) {
            $goodsWarehouseId = GoodsWarehouse::find()->where(['mall_id' => Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($array['keyword'], ['like', 'name', $array['keyword']])
                ->select('id');
        }
        /* @var FlashSaleGoods[] $flashSaleGoodsList */
        $goodsIdList = Goods::find()->where(['status' => 1, 'mall_id' => Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($goodsWarehouseId, ['goods_warehouse_id' => $goodsWarehouseId])
            ->select('id');
        $flashSaleGoodsList = FlashSaleGoods::find()->alias('p')->with('goods.goodsWarehouse')
            ->where(['p.is_delete' => 0, 'p.mall_id' => Yii::$app->mall->id, 'p.goods_id' => $goodsIdList])
            ->joinWith(
                [
                    'activity a' => function ($query) {
                        $query->where(
                            [
                                'AND',
                                ['a.mall_id' => Yii::$app->mall->id],
                                ['a.is_delete' => 0],
                                ['a.status' => 1],
                                ['>=', 'a.end_at', date('Y-m-d H:i:s')],
                            ]
                        );
                    }
                ]
            )
            ->with(['attr.attr', 'goods'])
            ->page($pagination)->all();
        $common = new CommonGoodsList();
        $newList = [];
        foreach ($flashSaleGoodsList as $flashSaleGoods) {
            $newItem = $common->getDiyBack($flashSaleGoods->goods);
            $newItem['attr'] = ArrayHelper::toArray($flashSaleGoods->attr);
            $newItem['use_attr'] = $flashSaleGoods->goods->use_attr;
            foreach ($flashSaleGoods->attr as $key => $value) {
                $newItem['attr'][$key]['attr'] = $value->attr;
            }
            list($discountType, $minDiscount) = self::getMinDiscount($newItem);
            $newItem = array_merge(
                $newItem,
                [
                    'min_discount' => $minDiscount,
                    'discount_type' => $discountType,
                    'sales' => $flashSaleGoods->goods->sales + $flashSaleGoods->goods->virtual_sales
                ]
            );
            $newList[] = $newItem;
        }
        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    public static function getPercentage($count, $flashSaleNum)
    {
        if ($count <= 0) {
            return 0;
        } elseif ($flashSaleNum == 0) {
            return 100;
        } else {
            return round(((int)$count / ((int)$flashSaleNum + (int)$count)) * 100, 2);
        }
    }

    public static function getMinDiscount($item)
    {
        if ($item['attr'][0]['attr']['type'] == 1) {
            $minDiscount = price_format($item['attr'][0]['attr']['discount'], 'string', 1);
            $discountType = 1;
        } else {
            $minDiscount = price_format($item['attr'][0]['attr']['cut'], 'string', 2);
            $discountType = 2;
        }
        $minPrice = $item['attr'][0]['price'];
        foreach ($item['attr'] as $key => $value) {
            if ($item['use_attr'] == 1) {
                if ($value['attr']['type'] == 1) {
                    if ($minDiscount > $value['attr']['discount']) {
                        $minDiscount = price_format($value['attr']['discount'], 'string', 1);
                    }
                } elseif ($value['attr']['type'] == 2) {
                    if ($minDiscount > $value['attr']['cut']) {
                        $minDiscount = price_format($value['attr']['cut'], 'string', 2);
                    }
                }
                if ($minPrice > $value['price']) {
                    $minPrice = $value['price'];
                }
            }
        }
        if (ceil($minDiscount) == $minDiscount) {
            $minDiscount = (int)$minDiscount;
        }
        return [$discountType, $minDiscount, $minPrice];
    }
}
