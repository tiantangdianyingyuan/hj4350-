<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api;

use app\forms\api\order\OrderException;
use app\forms\common\template\TemplateList;
use app\models\GoodsAttr;
use app\models\GoodsMemberPrice;
use app\models\OrderDetail;
use app\plugins\pintuan\forms\common\SettingForm;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanGoodsMemberPrice;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;
use yii\helpers\ArrayHelper;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName())
            ->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false)
            ->setSupportPayTypes($setting['payment_type']);

        return $this;
    }

    public function checkGoods($goods, $item)
    {
        /* @var PintuanGoods $pintuanGoods */
        $pintuanGoods = PintuanGoods::find()->where(['goods_id' => $goods->id])->one();
        if (!$pintuanGoods) {
            throw new OrderException('商品数据异常');
        }

        // 拼团限时判断
        if (strtotime($pintuanGoods->end_time) < time()) {
            throw new OrderException('拼团商品已过限时时间');
        }

        if ($this->form_data['list'][0]['pintuan_group_id'] == 0 && $pintuanGoods->is_alone_buy == 0) {
            throw new OrderException('商品不允许单买');
        }

        $buyCount = OrderDetail::find()->where([
            'goods_id' => $goods->id,
        ])->joinWith(['order' => function ($query) {
            $query->andWhere([
                'user_id' => \Yii::$app->user->id,
                'is_pay' => 1
            ]);
        }])->groupBy('order_id')->count();


        if ($pintuanGoods->groups_restrictions != -1 && $buyCount >= $pintuanGoods->groups_restrictions) {
            throw new OrderException('超出拼团次数限制');
        }

        if ($this->form_data['list'][0]['pintuan_group_id'] > 0 && $this->form_data['list'][0]['pintuan_order_id'] == 0) {
            $this->checkGroupNum($this->form_data['list'][0]['pintuan_group_id']);
        }
    }

    /**
     * 获取指定规格指定会员等级的会员价
     * @param OrderGoodsAttr $goodsAttr
     * @param $memberLevel
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getGoodsAttrMemberPrice($goodsAttr, $memberLevel)
    {
        $goodsMemberPrice = null;
        if ($goodsAttr->pintuan_group_id > 0) {
            /** @var PintuanGoodsAttr $pintuanGoodsAttr */
            $pintuanGoodsAttr = PintuanGoodsAttr::find()->where([
                'pintuan_goods_groups_id' => $goodsAttr->pintuan_group_id,
                'goods_attr_id' => $goodsAttr->goodsAttr->id,
                'is_delete' => 0
            ])->one();
            // 阶梯团会员价
            /** @var PintuanGoodsMemberPrice $pintuanGoodsMemberPrice */
            $pintuanGoodsMemberPrice = PintuanGoodsMemberPrice::find()->where([
                'pintuan_goods_attr_id' => $pintuanGoodsAttr->id,
                'pintuan_goods_groups_id' => $goodsAttr->pintuan_group_id,
                'level' => $memberLevel,
                'is_delete' => 0
            ])->one();
            if ($pintuanGoodsMemberPrice) {
                $goodsMemberPrice = $pintuanGoodsMemberPrice->price;
            }
        } else {
            // 单独购买会员价
            /** @var GoodsMemberPrice $goodsMemberPrice */
            $goodsMemberPrice = GoodsMemberPrice::find()->where([
                'goods_attr_id' => $goodsAttr->goodsAttr->id,
                'level' => $memberLevel,
                'is_delete' => 0,
            ])->one();
            if ($goodsMemberPrice) {
                $goodsMemberPrice = $goodsMemberPrice->price;
            }
        }

        return $goodsMemberPrice;
    }

    public function extraOrder($order, $mchItem)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($mchItem['goods_list'] as $goods) {
                /* @var OrderGoodsAttr $orderGoodsAttr */
                $orderGoodsAttr = $goods['goods_attr'];
                if ($orderGoodsAttr->pintuan_group_id > 0) {
                    $pintuanGroups = $orderGoodsAttr->pintuanGroup;
                    // 下单也需要判断商品
                    if ($orderGoodsAttr->pintuan_order_id > 0) {
                        /** @var PintuanOrders $pintuanOrder */
                        $pintuanOrder = PintuanOrders::find()->where([
                            'id' => $orderGoodsAttr->pintuan_order_id,
                            'status' => 1
                        ])->with('orderRelation.order')->one();
                        if (!$pintuanOrder) {
                            throw new OrderException('拼团活动ID：' . $orderGoodsAttr->pintuan_order_id . '不存在');
                        }

                        // if ($pintuanOrder->status)

                        /** @var PintuanOrderRelation $orItem */
                        foreach ($pintuanOrder->orderRelation as $orItem) {
                            if ($orItem->is_groups == 1 && $orItem->is_parent == 1) {
                                $endTime = strtotime($orItem->order->pay_time) + $pintuanOrder->pintuan_time * 60 * 60;
                                if ($endTime < time()) {
                                    throw new OrderException('拼团时间已过,无法参加');
                                }
                            }

                            if ($orItem->user_id == $order->user_id && $order->is_pay == 1) {
                                throw new OrderException('不能重复参加同一个拼团');
                            }
                        }
                    } else {
                        $pintuanOrder = new PintuanOrders();
                        $pintuanOrder->preferential_price = $pintuanGroups->preferential_price;
                        $pintuanOrder->mall_id = \Yii::$app->mall->id;
                        $pintuanOrder->people_num = $pintuanGroups->people_num;
                        $pintuanOrder->pintuan_time = $pintuanGroups->pintuan_time;
                        $pintuanOrder->pintuan_goods_groups_id = $pintuanGroups->id;
                        $pintuanOrder->goods_id = $pintuanGroups->goods_id;
                        $res = $pintuanOrder->save();

                        if (!$res) {
                            throw new OrderException($this->getErrorMsg($pintuanOrder));
                        }
                    }
                    $pintuanOrderId = $pintuanOrder->id;
                } else {
                    // 单独购买
                    $pintuanOrderId = 0;
                }
                $pintuanOrderRelation = new PintuanOrderRelation();
                $pintuanOrderRelation->order_id = $order->id;
                $pintuanOrderRelation->user_id = $order->user_id;
                $pintuanOrderRelation->is_parent = $orderGoodsAttr->pintuan_order_id ? 0 : 1;// 是否为团长
                $pintuanOrderRelation->is_groups = $pintuanOrderId > 0 ? 1 : 0;
                $pintuanOrderRelation->pintuan_order_id = $pintuanOrderId;
                $res = $pintuanOrderRelation->save();
                if (!$res) {
                    throw new OrderException($this->getErrorMsg($pintuanOrderRelation));
                }
            }
            $transaction->commit();
        } catch (OrderException $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getGoodsAttrClass()
    {
        $formData = $this->form_data;
        $newGoodsAttr = new OrderGoodsAttr();
        $newGoodsAttr->pintuan_group_id = $formData['list'][0]['pintuan_group_id'];
        $newGoodsAttr->pintuan_order_id = $formData['list'][0]['pintuan_order_id'];
        return $newGoodsAttr;
    }

    public function getSendType($mchItem)
    {
        $setting = (new SettingForm())->search();
        return $setting['send_type'];
    }

    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        $pintuanGroupId = $this->form_data['list'][0]['pintuan_group_id'];
        if ($pintuanGroupId) {
            $goodsAttr = $goodsAttr->pintuanGoodsAttr;
            if ($goodsAttr->pintuan_stock < $subNum) {
                throw new \Exception('商品库存不足，订单提交失败。');
            }
            $goodsAttr->pintuan_stock = $goodsAttr->pintuan_stock - $subNum;
            if ($goodsAttr->update(true, ['pintuan_stock']) === false) {
                throw new \Exception('商品库存更新失败。');
            }
        } else {
            if ($goodsAttr->stock < $subNum) {
                throw new \Exception('商品库存不足，订单提交失败。');
            }
            $goodsAttr->stock = $goodsAttr->stock - $subNum;
            if ($goodsAttr->update(true, ['stock']) === false) {
                throw new \Exception('商品库存更新失败。');
            }
        }
    }

    // 检测该拼团组 团长数量
    private function checkGroupNum($groupId)
    {
        $pintuanGroups = PintuanGoodsGroups::findOne($groupId);
        if (!$pintuanGroups) {
            throw new \Exception('拼团商品组不存在');
        }

        if ($pintuanGroups->group_num == 0) {
            return true;
        }

        $orderCount = PintuanOrders::find()->where([
            'pintuan_goods_groups_id' => $groupId,
        ])
            ->andWhere(['!=', 'status', 3])
            ->count();

        if ($orderCount >= $pintuanGroups->group_num) {
            throw new OrderException('开团已达上限，请参与其他伙伴的团');
        }
    }

    protected function getTemplateMessage()
    {
        $arr = ['pintuan_success_notice', 'pintuan_fail_notice', 'order_send_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
