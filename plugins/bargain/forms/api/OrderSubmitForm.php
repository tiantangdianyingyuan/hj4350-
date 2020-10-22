<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/19
 * Time: 10:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\common\ecard\CommonEcard;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\forms\common\CommonSetting;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\Plugin;

/**
 * @property BargainOrder $bargainOrder
 * @property BargainGoods $bargainGoods
 */
class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public $bargainOrder;
    public $bargainGoods;

    public function setPluginData()
    {
        $setting = CommonSetting::getCommon()->getList();
        $this->setSign((new Plugin())->getName())
            ->setSupportPayTypes($setting['payment_type']);

        $this->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false);
        $this->setEnableIntegral($setting['is_integral'] ? true : false);
        $this->setEnableMemberPrice(false);
        $this->setEnableCoupon($setting['is_coupon'] ? true : false);
        return $this;
    }

    protected function checkGoods($goods, $item)
    {
        $bargainGoods = BargainGoods::findOne(['goods_id' => $goods->id, 'is_delete' => 0]);
        if (!$bargainGoods) {
            throw new OrderException('砍价活动不存在');
        }

        if ($goods->status == 0) {
            throw new OrderException('砍价活动已关闭');
        }

        if (strtotime($bargainGoods->end_time) <= time()) {
            throw new OrderException('砍价活动已结束');
        }

        $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
        /* @var BargainOrder $bargainOrder */
        $bargainOrder = $commonBargainOrder->getUserOrder($bargainGoods->id, \Yii::$app->user->id);
        if (!$bargainOrder) {
            throw new OrderException('砍价已购买或不存在');
        }
        if ($bargainOrder->resetTime <= 0) {
            throw new OrderException('砍价活动已结束');
        }

        $bargainUserOrderList = $bargainOrder->userOrderList;
        $totalPrice = array_sum(array_column($bargainUserOrderList, 'price'));
        if (round($bargainOrder->price - $bargainOrder->min_price, 2) > round($totalPrice, 2) && $bargainGoods->type == 2) {
            throw new OrderException('不允许中途下单');
        }

        if ($bargainOrder->order) {
            throw new OrderException('已下单购买');
        }

        $this->bargainOrder = $bargainOrder;
        $this->bargainGoods = $bargainGoods;

        return true;
    }

    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $newGoodsAttr = parent::getGoodsAttr($goodsAttrId, $goods);

        $bargainOrder = $this->bargainOrder;
        $bargainUserOrderList = $bargainOrder->userOrderList;
        $totalPrice = array_sum(array_column($bargainUserOrderList, 'price'));
        $resetPrice = $bargainOrder->getNowPrice($totalPrice);
        $newGoodsAttr->price = $resetPrice;
        $newGoodsAttr->stock = CommonEcard::getCommon()->getEcardStock($this->bargainGoods->stock, $goods);
        $this->preferentialPrice += ($bargainOrder->price - $resetPrice);

        return $newGoodsAttr;
    }

    // 砍价下单 设置下单减库存
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        $bargainGoodsData = \Yii::$app->serializer->decode($this->bargainOrder->bargain_goods_data);
        if (isset($bargainGoodsData['stock_type']) && $bargainGoodsData['stock_type'] == 2) {
            $this->bargainGoods->stock -= 1;
            if (!$this->bargainGoods->save()) {
                throw new OrderException($this->getErrorMsg($this->bargainGoods));
            }
        }
        return true;
    }

    // 砍价下单 设置下单减库存时，需要判断库存
    public function checkGoodsStock($goodsList)
    {
        $bargainGoodsData = \Yii::$app->serializer->decode($this->bargainOrder->bargain_goods_data);
        $stock = CommonEcard::getCommon()->getEcardStock($this->bargainGoods->stock, $this->bargainGoods->goods);
        if (isset($bargainGoodsData['stock_type']) && $bargainGoodsData['stock_type'] == 2 && $stock <= 0) {
            throw new OrderException('砍价活动商品库存不足');
        }
        return true;
    }

    protected function getToken()
    {
        if (!$this->bargainOrder) {
            foreach ($this->form_data['list'] as $formMchItem) {
                $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
                /* @var BargainOrder $bargainOrder */
                $this->bargainOrder = $commonBargainOrder->getBargainOrder($formMchItem['bargain_order_id']);
                break;
            }
        }
        return $this->bargainOrder->token;
    }

    protected function getSendType($mchItem)
    {
        $setting = CommonSetting::getCommon()->getList();
        return $setting['send_type'];
    }

    public $preferentialPrice = 0; // 砍价优惠

    public function afterGetMchItem(&$mchItem)
    {
        $mchItem['insert_rows'][] = [
            'title' => '砍价优惠',
            'value' => '-¥' . $this->preferentialPrice,
            'data' => '-' . $this->preferentialPrice,
        ];
    }

    public function isPluginCouponUsable($mchItem, $userCoupon)
    {
        $price = floatval($mchItem['total_goods_original_price']) - $this->preferentialPrice;
        if ($price < $userCoupon->coupon_min_price) {
            return false;
        }
        return true;
    }

    public function extraOrder($order, $mchItem)
    {
        $this->bargainOrder->preferential_price = price_format($this->preferentialPrice);
        if (!$this->bargainOrder->save()) {
            throw new OrderException($this->getErrorMsg($this->bargainOrder));
        }
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
