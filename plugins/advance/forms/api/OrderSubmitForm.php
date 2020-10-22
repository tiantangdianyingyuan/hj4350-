<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\MallMembers;
use app\models\Order;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserIdentity;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\Plugin;
use app\plugins\vip_card\models\VipCardDetail;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName())->setEnablePriceEnable(false)
            ->setSupportPayTypes($setting['payment_type'])
            ->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false)
            ->setEnableCoupon($setting['is_coupon'] ? true : false)
            ->setEnableMemberPrice($setting['is_member_price'] ? true : false)
            ->setEnableIntegral($setting['is_integral'] ? true : false)
            ->setEnableFullReduce($setting['is_full_reduce'] ? true : false);
        return $this;
    }

    public function setExtraDiscountData($mchItem, $formMchItem)
    {
        foreach ($mchItem['goods_list'] as &$item) {
            /** @var Goods $goods */
            $goods = Goods::find()->with('goodsWarehouse')->where([
                'id' => $item['id'],
                'mall_id' => \Yii::$app->mall->id,
                'status' => 1,
                'is_delete' => 0,
            ])->one();
            //判断是否在付尾款时间内
            $advance_goods = AdvanceGoods::findOne(['goods_id' => $item['id'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            if (!$advance_goods) {
                throw new OrderException('预售商品已下架。');
            }
            if ($advance_goods->pay_limit != -1) {
                $time = strtotime($advance_goods->end_prepayment_at) + (60 * 60 * 24 * $advance_goods->pay_limit);
                if ($time < time() || strtotime($advance_goods->end_prepayment_at) > time()) {
                    throw new OrderException('现在不在付尾款时间内。');
                }
            }
            //尾款计算
            $order_model = AdvanceOrder::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $item['id'],
                'is_pay' => 1,
                'is_cancel' => 0,
                'is_refund' => 0,
                'is_delete' => 0,
                'is_recycle' => 0
            ]);
            $advance_order_count = $goods->virtual_sales + $order_model->sum('goods_num');
            /* @var AdvanceOrder $order_info */
            $order_info = $order_model->andWhere(['id' => $this->advance_id])->one();
            if (!$order_info) {
                throw new OrderException('定金订单不存在。');
            }
            if ($order_info->order_id != 0) {
                throw new OrderException('该预售，您有未支付的尾款订单，请去个人中心-待付款订单支付。');
            }
            $discount = 10;//初始10折，等于没有优惠折扣
            if (!is_array($advance_goods->ladder_rules)) {
                $advance_goods->ladder_rules = json_decode($advance_goods->ladder_rules, true);
            }
            foreach ($advance_goods->ladder_rules as $value) {
                if ($advance_order_count >= $value['num']) {
                    $discount = $value['discount'];
                }
            }

            $item['ladder_discount'] = $discount;
            $item['deposit'] = $order_info->deposit;
            $item['swell_deposit'] = $order_info->swell_deposit;

            //初始化
            $item['preferential_price'] = 0;
        }
        return $mchItem;
    }

    public function afterGetMchItem(&$mchItem)
    {
        $deposit = 0;
        $swell_deposit = 0;
        $preferential_price = 0;
        foreach ($mchItem['goods_list'] as $goodsItem) {
            $deposit += bcmul($goodsItem['deposit'], $goodsItem['num']);
            $swell_deposit += bcmul($goodsItem['swell_deposit'], $goodsItem['num']);
            $preferential_price += bcmul($goodsItem['preferential_price'], $goodsItem['num']);

            //记录优惠金额
            $order_model = AdvanceOrder::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $goodsItem['id'],
                'is_pay' => 1,
                'is_cancel' => 0,
                'is_refund' => 0,
                'is_delete' => 0,
                'is_recycle' => 0,
                'id' => $this->advance_id])
                ->one();

            $order_model->preferential_price = $goodsItem['preferential_price'] * $goodsItem['num'];
            if (!$order_model->save()) {
                throw new OrderException('活动优惠金额保存失败。' . $order_model->errors[0]);
            }
        }
        if ($preferential_price > 0) {
            $mchItem['insert_rows'][] = [
                'title' => '阶梯抵扣',
                'value' => '-￥' . $preferential_price,
                'data' => price_format(-$preferential_price),
            ];
        }
        $mchItem['insert_rows'][] = [
            'title' => '定金抵扣',
            'value' => '-￥' . price_format($swell_deposit),
            'data' => price_format(-$swell_deposit),
        ];

        //起送规则默认值
        $mchItem['pick_up_enable'] = true;
        $mchItem['pick_up_price'] = 0;

        // $mchItem = $this->setOrderForm($mchItem);
        $mchItem = $this->setGoodsForm($mchItem);
        return $mchItem;
    }

    private $advance_id;

    protected function getGoodsItemData($item)
    {
        $this->advance_id = $item['advance_id'];
        return parent::getGoodsItemData($item); // TODO: Change the autogenerated stub
    }

    // 商品规格类
    public function getGoodsAttrClass()
    {
        return new AdvanceOrderGoodsAttr();
    }

    public function getSendType($mchItem)
    {
        $setting = (new SettingForm())->search();
        return $setting['send_type'];
    }

    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return;//预售在定金阶段已扣除库存
    }

    public function checkGoodsStock($goodsList)
    {
        return true;
    }

    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    protected function checkGoodsBuyLimit($goodsList)
    {
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
        }
    }

    public function getToken()
    {
        //与定金token共用，唯一值，且尾款订单只有单商品，故取[0]
        $advance_info = AdvanceOrder::findOne($this->advance_id);
        $advance_info->order_token = parent::getToken();
        if (!$advance_info->save()) {
            throw new OrderException('order_token保存失败');
        }
        return $advance_info->order_token;
    }

    protected function setCouponDiscountData($mchItem, $formMchItem)
    {
        $mchItem['coupon'] = [
            'enabled' => true,
            'use' => false,
            'coupon_discount' => price_format(0),
            'user_coupon_id' => 0,
            'coupon_error' => null,
        ];
        if (!$this->getEnableCoupon()) {
            return $mchItem;
        }
        if ($mchItem['mch']['id'] != 0) { // 入住商不可使用优惠券
            $mchItem['coupon']['enabled'] = false;
            return $mchItem;
        }
        if (empty($formMchItem['user_coupon_id'])) {
            return $mchItem;
        }
        $nowDateTime = date('Y-m-d H:i:s');
        /** @var UserCoupon $userCoupon */
        $userCoupon = UserCoupon::find()->where([
            'AND',
            ['id' => $formMchItem['user_coupon_id']],
            ['user_id' => \Yii::$app->user->identity->getId()],
            ['is_delete' => 0],
            ['is_use' => 0],
            ['<=', 'start_time', $nowDateTime],
            ['>=', 'end_time', $nowDateTime],
        ])->one();
        if (!$userCoupon) {
            $mchItem['coupon']['coupon_error'] = '优惠券不存在';
            return $mchItem;
        }
        if ($userCoupon->coupon_min_price > $mchItem['total_goods_price']) { // 可用的商品原总价未达到优惠券使用条件
            $mchItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
            return $mchItem;
        }
        return parent::setCouponDiscountData($mchItem, $formMchItem);
    }

    protected function setIntegralDiscountData($mchItem, $formMchItem)
    {
        //优惠卷门槛为售价、会员价，阶梯折扣后，所以定金优惠放优惠卷后面进行
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            //定金优惠
            $goodsItem['total_price'] = price_format(bcsub($goodsItem['total_price'], bcmul($goodsItem['swell_deposit'], $goodsItem['num'])));
            $mchItem['total_goods_price'] = price_format(bcsub($mchItem['total_goods_price'], bcmul($goodsItem['swell_deposit'], $goodsItem['num'])));

            //10折直接跳过
            if ($goodsItem['ladder_discount'] == 10) {
                continue;
            }
            /* @var OrderGoodsAttr $goodsAttr */
            $goodsAttr = $goodsItem['goods_attr'];
            $goodsItem['preferential_price'] = bcsub($goodsAttr->price,
                                                     bcdiv(bcmul($goodsAttr->price, $goodsItem['ladder_discount']), 10)
            );
            //阶梯优惠
            $goodsItem['total_price'] = price_format(bcsub($goodsItem['total_price'], $goodsItem['preferential_price']));
            $mchItem['total_goods_price'] = price_format(bcsub($mchItem['total_goods_price'], $goodsItem['preferential_price']));

            $goodsItem['total_price'] = $goodsItem['total_price'] >= 0 ? $goodsItem['total_price'] : 0;
            $mchItem['total_goods_price'] = $mchItem['total_goods_price'] >= 0 ? $mchItem['total_goods_price'] : 0;
        }
        return parent::setIntegralDiscountData($mchItem, $formMchItem); // TODO: Change the autogenerated stub
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
