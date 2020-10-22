<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonMallMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsMemberPrice;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Model;
use app\models\OrderDetail;
use app\models\UserIdentity;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\step\models\GoodsAttr;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $cat_id;
    public $keyword;
    public $page;
    public $group_id;

    public function rules()
    {
        return [
            [['id', 'cat_id', 'group_id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // TODO MYSQL此处查询过多
        $groupGoodsIds = PintuanGoodsGroups::find()->where([
            'is_delete' => 0,
        ])->groupBy('goods_id')->select('goods_id');

        $goodsIds = PintuanGoods::find()->andWhere([
            '>', 'end_time', mysql_timestamp(),
        ])->andWhere(['goods_id' => $groupGoodsIds, 'start_time' =>  '0000-00-00 00:00:00'])->select('goods_id');

        $query = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            'status' => 1
        ]);
        if ($this->cat_id == 0) {
            $goodsIds->andWhere(['is_sell_well' => 1]);
        } else {
            $goodsWarehouseIds = $this->getCatGoods($this->cat_id, $goodsIds);
            $query->andWhere(['goods_warehouse_id' => $goodsWarehouseIds]);
        }

        $list = $query->with(['pintuanOrder' => function ($query) {
            $query->andWhere(['status' => 2]);
        }])
            ->with('groups.attr')
            ->andWhere(['id' => $goodsIds])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
            ->page($pagination, 10)->all();
        $newList = [];
        /* @var Goods[] $list */
        foreach ($list as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $goodsCount = OrderDetail::find()->where([
                'goods_id' => $item['id'],
                'refund_status' => 0,
            ])->joinWith(['order' => function ($query) {
                $query->andWhere(['status' => 1, 'mall_id' => \Yii::$app->mall->id]);
            }])->sum('num');
            $newItem = array_merge($newItem, [
                'name' => $item->goodsWarehouse->name,
                'cover_pic' => $item->goodsWarehouse->cover_pic,
                'original_price' => $item->goodsWarehouse->original_price,
                'virtual_sales' => $item->virtual_sales + $goodsCount,
                'video_url' => $item->goodsWarehouse->video_url,
            ]);

            $attrGroup = \Yii::$app->serializer->decode($item->attr_groups);
            $attrList = $item->resetAttr($attrGroup);

            $groupMinPrice = 0;
            $groupMinMemberPrice = 0;
            if (isset($item->groups[0])) {
                try {
                    $newAttr_2 = [];
                    /** @var PintuanGoodsAttr $attr */
                    foreach ($item->groups[0]->attr as $attr) {
                        $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $attr->pintuan_price) : $attr->pintuan_price;

                        $newAttrItem = ArrayHelper::toArray($attr);
                        $newAttrItem['attr_list'] = $attrList[$attr->goodsAttr->sign_id];
                        $newAttrItem['price_member'] = 0;
                        $newAttrItem['price'] = $attr->goodsAttr->price;
                        $newAttrItem['member_price_list'] = ArrayHelper::toArray($attr->memberPrice);
                        $newAttr_2[] = $newAttrItem;
                    }
                    $defaultNewAttr = $this->getMemberPrice($newAttr_2, $item);

                    foreach ($defaultNewAttr as $newAttr) {
                        if (!$groupMinMemberPrice) {
                            $groupMinMemberPrice = $newAttr['price_member'];
                        }
                        $groupMinMemberPrice = min($newAttr['price_member'], $groupMinMemberPrice);
                    }
                } catch (\Exception $exception) {
                }
            }
            $pintuanCommonGoods = new CommonGoods();
            $newItem['level_price'] = $groupMinMemberPrice;
            $newItem['price'] = $groupMinPrice ? $groupMinPrice : $newItem['price'];
            $newItem['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($item)->getAppoint();

            $goodsStock = 0;
            try {
                foreach ($item->attr as $aItem) {
                    $goodsStock += $aItem->stock;
                }
                foreach ($item->groups as $gItem) {
                    foreach ($gItem->attr as $aItem) {
                        $goodsStock += $aItem->pintuan_stock;
                    }
                }
            } catch (\Exception $exception) {
            }
            $newItem['goods_stock'] = $goodsStock;

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
        $goodsCats = GoodsCats::find()->where([
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'mch_id' => 0, 'status' => 1
        ]);
        if ($id) {
            $goodsCats2 = (new Query())->from(['gc2' => $goodsCats])->where([
                'gc2.parent_id' => $id
            ])->select('id');
            $catIds = (new Query())->from(['gc' => $goodsCats])->where([
                'or',
                ['gc.id' => $id],
                ['gc.parent_id' => $id],
                ['gc.parent_id' => $goodsCats2]
            ])->select('id');
        } else {
            $goodsCats = $goodsCats->select('id')->all();
            $catIds = array_column($goodsCats, 'id');
        }

        $catGoodsIds = GoodsCatRelation::find()->alias('gc')->where([
            'gc.cat_id' => $catIds,
            'gc.is_delete' => 0
        ])->select('gc.goods_warehouse_id');

        return $catGoodsIds;
    }

    public function detail()
    {
        try {
            $common = \app\forms\common\goods\CommonGoodsDetail::getCommonGoodsDetail(\Yii::$app->mall);
            $common->user = \Yii::$app->user->identity;
            /* @var Goods $goods */
            $goods = Goods::find()->alias('g')->where([
                'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
                'g.is_delete' => 0,
                'g.mall_id' => \Yii::$app->mall->id,
                'g.id' => $this->id
            ])->with(['attr.share', 'share', 'attr.memberPrice', 'cards', 'services', 'groups.attr.memberPrice', 'pintuanGoods'])
                ->one();
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            //todo 过公共方法，用于记录足迹，需优化
            $common->getGoods($this->id);

            $common->goods = $goods;
            $attrGroup = \Yii::$app->serializer->decode($common->goods->attr_groups);
            $attrList = $common->goods->resetAttr($attrGroup);
            /* @var PintuanGoodsGroups[] $pintuanGroups */
            $pintuanGroups = PintuanGoodsGroups::find()->with(['attr.goodsAttr', 'attr.memberPrice'])
                ->where(['is_delete' => 0, 'goods_id' => $this->id])->all();
            $groupMinPrice = 0;
            $groupMaxPrice = 0;
            $groupMinMemberPrice = 0;
            if (count($pintuanGroups) > 0) {
                $newAttr_2 = [];
                /** @var PintuanGoodsAttr $attr */
                foreach ($pintuanGroups[0]->attr as $attr) {
                    $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $attr->pintuan_price) : $attr->pintuan_price;
                    $groupMaxPrice = $groupMaxPrice ? max($groupMaxPrice, $attr->pintuan_price) : $attr->pintuan_price;

                    $newItem = ArrayHelper::toArray($attr);
                    $newItem['attr_list'] = $attrList[$attr->goodsAttr->sign_id];
                    $newItem['price_member'] = 0;
                    $newItem['price'] = $attr->goodsAttr->price;
                    $newItem['member_price_list'] = ArrayHelper::toArray($attr->memberPrice);
                    $newAttr_2[] = $newItem;
                }
                $defaultNewAttr = $this->getMemberPrice($newAttr_2, $common->goods);

                foreach ($defaultNewAttr as $newAttr) {
                    if (!$groupMinMemberPrice) {
                        $groupMinMemberPrice = $newAttr['price_member'];
                    }
                    $groupMinMemberPrice = min($newAttr['price_member'], $groupMinMemberPrice);
                }
            }

            if ($this->group_id) {
                $group = null;
                foreach ($pintuanGroups as $pintuanGroup) {
                    if ($pintuanGroup->id == $this->group_id) {
                        $group = $pintuanGroup;
                        break;
                    }
                }
                if (!$group) {
                    throw new \Exception('阶梯团不存在或已删除');
                }
                $newAttr = [];
                foreach ($group->attr as $attr) {
                    $newItem = ArrayHelper::toArray($attr->goodsAttr);
                    $newItem['price'] = $attr->pintuan_price;
                    $newItem['stock'] = $attr->pintuan_stock;
                    $newItem['member_price_list'] = ArrayHelper::toArray($attr->memberPrice);
                    $newAttr[] = $newItem;
                }

                $newAttr_2 = [];
                /* @var GoodsAttr[] $attr */
                foreach ($newAttr as $key => $item) {
                    $newItem = $item;
                    $newItem['attr_list'] = $attrList[$item['sign_id']];
                    $newItem['price_member'] = 0;
                    $newAttr_2[] = $newItem;
                }

                $res = $common->getAll(['goods_num', 'goods_no', 'goods_weight', 'attr_group', 'option', 'services',
                    'cards', 'price_min', 'price_max', 'pic_url', 'share', 'sales', 'favorite', 'goods_marketing', 'goods_marketing_award','vip_card_appoint'
                ]);
                $res['attr'] = $this->getMemberPrice($newAttr_2, $common->goods);
            } else {
                $res = $common->getAll();
            }

            if (isset($res['goods_marketing_award']['integral']['title']) && $res['goods_marketing_award']['integral']['title'] && $res['give_integral_type'] == 2) {
                $price = bcmul($groupMaxPrice, $res['give_integral'] / 100);
                $res['goods_marketing_award']['integral']['title'] = preg_replace_callback(
                    '/^(\D+)(\d*)(\D+)$/',
                    function ($matches) use ($price) {
                        return $matches[1] . max([$price, $matches[2]]) . $matches[3];
                    },
                    $res['goods_marketing_award']['integral']['title']
                );
            }
            $res['group_min_member_price'] = $groupMinMemberPrice;
            $res['group_price_min'] = $groupMinPrice;
            $res['group_price_max'] = $groupMaxPrice;
            $res['pintuan_groups'] = $pintuanGroups;
            $res['pintuanGoods'] = $goods->pintuanGoods;
            $res['original_price'] = $goods->originalPrice;
            $setting = (new SettingForm())->search();
            $res['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $res['goods_marketing']['limit'] : '';

            $goodsStock = 0;
            foreach ($goods->attr as $aItem) {
                $goodsStock += $aItem->stock;
            }
            foreach ($goods->groups as $gItem) {
                foreach ($gItem->attr as $aItem) {
                    $goodsStock += $aItem->pintuan_stock;
                }
            }
            $res['goods_stock'] = $goodsStock;

            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $res['share'] = 0;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'detail' => $res,
                    'pintuan_list' => $this->getPintuanList($common->goods->id),
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

    private function getPintuanList($goodsId)
    {
        $list = PintuanOrders::find()->where([
            'status' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $goodsId,
        ])
            ->with('goods.goodsWarehouse', 'orderRelation.user.userInfo', 'orderRelation.order')
            ->limit(10)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['goods']['pic_url'] = \Yii::$app->serializer->decode($item['goods']['goodsWarehouse']['pic_url']);
            $item['goods']['attr_groups'] = \Yii::$app->serializer->decode($item['goods']['attr_groups']);

            $newItemList = [];
            foreach ($item['orderRelation'] as $orItem) {
                if (($orItem['order']['is_pay'] == 1 || $orItem['order']['pay_type'] == 2) || $orItem['robot_id'] > 0) {
                    if ($orItem['is_parent'] == 1) {
                        $orItem['user']['avatar'] = $orItem['user']['userInfo']['avatar'];
                        unset($orItem['user']['userInfo']);
                        $item['group_user'] = $orItem;
                    }
                    $newItemList[] = $orItem;
                }
            }
            $item['orderRelation'] = $newItemList;
            $pintuanTime = strtotime($item['created_at']) + $item['pintuan_time'] * 60 * 60;
            $item['surplus_people'] = (int)($item['people_num'] - count($item['orderRelation']));
            $item['surplus_time'] = ($pintuanTime - time()) > 0 ? $pintuanTime - time() : 0;
            $item['surplus_date_time'] = date('Y-m-d H:i:s', $pintuanTime);
            // TODO 需排除已过拼团时间、但是拼团状态还是拼团中的订单
            unset($item['orderRelation']);
        }

        return $list;
    }

    /**
     * 获取各个会员等级的会员价
     * @param $attr
     * @return mixed
     */
    private function getMemberPrice($attr, $goods)
    {
        $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
        /* @var MallMembers[] $members */
        $members = CommonMallMember::getAllMember();
        /* @var GoodsAttr[] $goodsAttr */
        foreach ($attr as &$item) {
            $first = true;
            $newPrice = isset($item['pintuan_price']) ? $item['pintuan_price'] : $item['price'];
            $item['price_member'] = $newPrice;
            foreach ($members as $member) {
                if ($goods->is_level_alone == 1) {
                    /* @var GoodsMemberPrice[] $memberPriceList */
                    $memberPriceList = $item['member_price_list'];
                    foreach ($memberPriceList as $value) {
                        if ($value['level'] == $member->level) {
                            $item['member_price_' . $member->level] = $value['price'];
                            break;
                        } else {
                            $item['member_price_' . $member->level] = round($newPrice * $member->discount / 10, 2);
                        }
                    }
                } else {
                    $item['member_price_' . $member->level] = round($newPrice * $member->discount / 10, 2);
                }
                $item['price_member'] = min($item['price_member'], $item['member_price_' . $member->level]);
            }
            if ($userIdentity && $userIdentity->member_level) {
                $key = 'member_price_' . $userIdentity->member_level;
                if (isset($item[$key])) {
                    $item['price_member'] = $item[$key];
                }
            }
        }
        unset($item);
        return $attr;
    }
}
