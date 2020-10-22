<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api\v2;

use app\forms\api\order\OrderException;
use app\forms\common\CommonMallMember;
use app\forms\common\template\TemplateList;
use app\models\GoodsMemberPrice;
use app\plugins\pintuan\forms\common\v2\SettingForm;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName());
        $this->setSupportPayTypes($setting['payment_type']);
        $this->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false);
        $this->setEnableIntegral($setting['is_integral'] ? true : false);
        $this->setEnableMemberPrice($setting['is_member_price'] ? true : false);
        $this->setEnableCoupon($setting['is_coupon'] ? true : false);
        $this->setEnableVipPrice($setting['svip_status'] ? true : false);
        $this->setEnableFullReduce($setting['is_full_reduce'] ? true : false);

        return $this;
    }

    public function checkGoods($goods, $item)
    {
        /* @var PintuanGoods $pintuanGoods */
        $pintuanGoods = PintuanGoods::find()->where(['goods_id' => $goods->id])->one();
        if (!$pintuanGoods) {
            throw new OrderException('拼团商品不存在');
        }

        // 拼团限时判断
        if (strtotime($pintuanGoods->start_time) > time()) {
            throw new OrderException('拼团活动未开始');
        }
        if ($pintuanGoods->end_time != '0000-00-00 00:00:00' && strtotime($pintuanGoods->end_time) < time()) {
            throw new OrderException('拼团活动已结束');
        }

        if ($this->form_data['list'][0]['pintuan_group_id'] == 0 && $pintuanGoods->is_alone_buy == 0) {
            throw new OrderException('商品不允许单买');
        }

        if ($this->form_data['list'][0]['pintuan_group_id'] > 0 && $this->form_data['list'][0]['pintuan_order_id'] == 0) {
            $this->checkGroupNum($this->form_data['list'][0]['pintuan_group_id']);
        }
    }

    /**
     * @param OrderGoodsAttr $goodsAttr
     * @param $memberLevel
     * @return GoodsMemberPrice|null
     * @throws OrderException
     */
    protected function getGoodsAttrMemberPrice($goodsAttr, $memberLevel)
    {
        // TODO 会员价需检查
        $goodsMemberPrice = CommonMallMember::getGoodsAttrMemberPrice($goodsAttr->goodsAttr, $memberLevel);
        // $goodsMemberPrice 有可能为空
        return $goodsMemberPrice ? $goodsMemberPrice->price : null;
    }

    public function extraOrder($order, $mchItem)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            foreach ($mchItem['goods_list'] as $goods) {
                /* @var OrderGoodsAttr $orderGoodsAttr */
                $orderGoodsAttr = $goods['goods_attr'];
                if ($orderGoodsAttr->pintuan_group_id > 0) {
                    $pintuanGroups = $orderGoodsAttr->pintuanGroup;
                    // 下单也需要判断商品
                    if ($orderGoodsAttr->pintuan_order_id > 0) {
                        /** @var PintuanOrders $pintuanOrder */
                        $pintuanOrder = PintuanOrders::find()->where(['id' => $orderGoodsAttr->pintuan_order_id, 'status' => 1])
                            ->with('orderRelation.order')
                            ->one();
                        if (!$pintuanOrder) {
                            throw new OrderException('拼团活动ID：' . $orderGoodsAttr->pintuan_order_id . '不存在');
                        }

                        /** @var PintuanOrderRelation $orItem */
                        foreach ($pintuanOrder->orderRelation as $orItem) {
                            if ($orItem->is_groups == 1 && $orItem->is_parent == 1) {
                                $endTime = strtotime($orItem->order->pay_time) + $pintuanOrder->pintuan_time * 60 * 60;
                                if ($endTime < time()) {
                                    throw new OrderException('参团时间已结束,无法拼团');
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
                        $pintuanOrder->expected_over_time = time() + $pintuanGroups->pintuan_time * 60 * 60;
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
                $pintuanOrderRelation->is_parent = $orderGoodsAttr->pintuan_order_id ? 0 : 1; // 是否为团长
                $pintuanOrderRelation->is_groups = $pintuanOrderId > 0 ? 1 : 0;
                $pintuanOrderRelation->pintuan_order_id = $pintuanOrderId;
                $res = $pintuanOrderRelation->save();
                if (!$res) {
                    throw new OrderException($this->getErrorMsg($pintuanOrderRelation));
                }
            }
        } catch (OrderException $e) {
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

    // 检测该拼团组
    private function checkGroupNum($groupId)
    {
        $pintuanGroups = PintuanGoodsGroups::findOne(['id' => $groupId, 'is_delete' => 0]);
        if (!$pintuanGroups) {
            throw new \Exception('拼团商品组不存在');
        }

        if ($pintuanGroups->goods_id != $this->form_data['list'][0]['goods_list'][0]['id']) {
            throw new OrderException('拼团组与商品ID不符');
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

    public function afterGetMchItem(&$mchItem)
    {
        parent::afterGetMchItem($mchItem);
        $this->setPintuanInsertRows($mchItem);
    }

    private function setPintuanInsertRows(&$mchItem)
    {
        $discounts = [];
        foreach ($mchItem['goods_list'] as $goods) {
            if (!isset($goods['goods_attr'])) {
                continue;
            }

            $goodsAttr = $goods['goods_attr'];
            if (!isset($goodsAttr['discount']) || !is_array($goodsAttr['discount'])) {
                continue;
            }

            $ptDiscounts = $goodsAttr['discount'];
            foreach ($ptDiscounts as $d) {
                if (!isset($discounts[$d['name']])) {
                    $discounts[$d['name']] = $goodsAttr['number'] * $d['value'];
                } else {
                    $discounts[$d['name']] += $d['value'];
                }
            }
        }
        foreach ($discounts as $k => $v) {
            if (!isset($mchItem['insert_rows'])) {
                $mchItem['insert_rows'] = [];
            }
            $mchItem['insert_rows'][] = [
                'title' => $k,
                'value' => $v > 0 ? ('+¥' . price_format($v)) : ('-¥' . price_format(0 - $v)),
                'data' => price_format($v),
            ];
        }
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
