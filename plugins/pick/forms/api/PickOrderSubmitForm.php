<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\pick\forms\api;


use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\models\Order;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickGoods;
use app\plugins\pick\models\PickSetting;

class PickOrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public $pick_activity_id;

    public function setPluginData()
    {
        $setting = PickSetting::getList(\Yii::$app->mall->id);

        $this->setSupportPayTypes(!empty($setting) ? ($setting['payment_type'] ?? ['online_pay']) : ['online_pay'])
            ->setEnableMemberPrice(false)->setEnableCoupon(false)
            ->setEnableIntegral(false)//->setEnableVipPrice(true)//为了享受超级会员卡包邮
            ->setEnableAddressEnable(!empty($setting) ? ($setting['is_territorial_limitation'] ? true : false) : false);
        return $this;
    }

    /**
     * N元任选优惠
     * @param $mchItem
     * @param $formMchItem
     * @return mixed
     * @throws OrderException
     */
    public function setExtraDiscountData($mchItem, $formMchItem)
    {
        if (!$this->pick_activity_id && empty($formMchItem['pick_activity_id'])) {
            throw new OrderException('缺少活动ID');
        }
        $this->pick_activity_id = $formMchItem['pick_activity_id'];

        $mchItem['pick_discount'] = price_format(0);

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        /** @var UserIdentity $identity */
        $identity = $user->getIdentity()->andWhere(['is_delete' => 0,])->one();
        if (!$identity) {
            return $mchItem;
        }

        //是否符合活动条件
        $activity = PickActivity::findOne(['id' => $this->pick_activity_id, 'is_delete' => 0]);
        if (strtotime($activity->start_at) > time() && strtotime($activity->end_at) < time()) {
            throw new OrderException('不在当前活动时间。');
        }
        if ($activity->status == 0) {
            throw new OrderException('该活动已下架。');
        }
        //总件数，总商品金额
        $count = 0;
        $price = 0;
        foreach ($mchItem['goods_list'] as $item) {
            //判断商品是否属于该活动
            if (empty(PickGoods::findOne(
                ['pick_activity_id' => $this->pick_activity_id, 'goods_id' => $item['id'], 'status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]
            ))) {
                throw new OrderException($item['goods_attr']['name'] . '-不属于当前活动商品。');
            }

            $count += $item['num'];
            /* @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $item['goods_attr'];
            $price += price_format($goodsAttr->price * $item['num']);
        }
        if (!is_int($count / $activity->rule_num)) {
            throw new OrderException('商品件数不符合活动规则，请选取活动整数倍的商品件数。');
        }

        $totalSubPrice = 0; // 总计优惠金额
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            $pickUnitPrice = null;
            $discountName = 'N元任选优惠';

            $goodsItem['pick_discount'] = price_format(0);

            /* @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $goodsItem['goods_attr'];

            //分销佣金算法参与比例分佣金，案例：
            //300元任选3件，A商品92元，B商品104元，C商品120元
            //则按照A商品300*92/（92+104+120）=87.34元、
            //B商品300*104/（92+104+120）=98.73元
            //C商品300*120/（92+104+120=113.92元  计算佣金
            $pickUnitPrice = $price == 0
                ? price_format($activity->rule_price / $activity->rule_num)
                : price_format($activity->rule_price * ($count / $activity->rule_num) * $goodsAttr->price / $price);

            if ($pickUnitPrice && is_numeric($pickUnitPrice) && $pickUnitPrice >= 0) {
                // 商品单件价格（N选优惠后）
                $goodsAttr->price = price_format($pickUnitPrice);
                $pickTotalPrice = price_format($pickUnitPrice * $goodsItem['num']);
                $pickSubPrice = $goodsItem['total_original_price'] - $pickTotalPrice;
                if ($pickSubPrice != 0) {
                    // 减去优惠金额
                    $pickSubPrice = min($goodsItem['total_price'], $pickSubPrice);
                    $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $pickSubPrice);
                    $totalSubPrice += $pickSubPrice;
//                    $goodsItem['discounts'][] = [
//                        'name' => $discountName,
//                        'value' => $pickSubPrice > 0 ?
//                            ('-' . price_format($pickSubPrice))
//                            : ('+' . price_format(0 - $pickSubPrice))
//                    ];
                    $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $pickSubPrice);
                    $goodsItem['pick_discount'] = price_format($pickSubPrice);
                }
            }
        }
        if ($totalSubPrice) {
            $mchItem['pick_discount'] = price_format($totalSubPrice);
        }
        //优惠后，因为四舍五入问题导致的比活动价格多或少几分钱时，记录第一件商品优惠中
        $redundancy_price = $mchItem['total_goods_price'] - ($activity->rule_price * ($count / $activity->rule_num));
        if ($redundancy_price != 0) {
            $mchItem['pick_discount'] = price_format($mchItem['pick_discount'] + $redundancy_price);
            $mchItem['goods_list'][0]['pick_discount'] = price_format($mchItem['goods_list'][0]['pick_discount'] + $redundancy_price);
            $mchItem['goods_list'][0]['total_price'] = price_format($mchItem['goods_list'][0]['total_price'] - $redundancy_price);
//            $mchItem['goods_list'][0]['discounts'][0]['value'] = price_format($mchItem['goods_list'][0]['discounts'][0]['value'] - $redundancy_price);
            $mchItem['goods_list'][0]['goods_attr']['price'] = price_format($mchItem['goods_list'][0]['goods_attr']['price'] - $redundancy_price);
            $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $redundancy_price);
        }
        $insertRowValue = $mchItem['pick_discount'] > 0 ?
            ('-' . price_format($mchItem['pick_discount']))
            : (price_format(0 - $mchItem['pick_discount']));
        $mchItem['insert_rows'][] = [
            'title' => 'N元任选优惠',
            'value' => $insertRowValue < 0 ? ('-¥' . price_format(0 - $insertRowValue)) : ('+¥' . $insertRowValue),
            'data' => $insertRowValue,
        ];

        return $mchItem;
    }

    protected function getSendType($mchItem)
    {
        $setting = PickSetting::getList(\Yii::$app->mall->id);

        return !empty($setting) ? ($setting['send_type'] ?? ['express']) : ['express'];
    }

    /**
     * 优惠券优惠
     * @param $mchItem
     * @param $formMchItem
     * @return mixed
     * @throws OrderException
     */
    protected function setCouponDiscountData($mchItem, $formMchItem)
    {
        $mchItem['coupon'] = [
            'enabled' => false,
            'use' => false,
            'coupon_discount' => price_format(0),
            'user_coupon_id' => 0,
            'coupon_error' => null,
        ];
        return $mchItem;
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
