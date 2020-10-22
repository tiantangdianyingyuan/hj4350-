<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\api;

use app\core\response\ApiCode;
use app\forms\api\order\OrderException;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\jobs\GiftOrderSubmitJob;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;
use app\plugins\gift\models\GiftSetting;

class GiftOrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public $id;
    public $type;
    public $open_time;
    public $open_num;
    public $open_type;
    public $bless_word;
    public $bless_music;

    public function setPluginData()
    {
        $setting = GiftSetting::search();
        $this->setEnablePriceEnable(false)
            ->setSupportPayTypes($setting ? json_decode($setting['payment_type'], true) : "['online_pay']")
            ->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false)
            ->setEnableCoupon($setting['is_coupon'] ? true : false)
            ->setEnableMemberPrice($setting['is_member_price'] ? true : false)
            ->setEnableIntegral($setting['is_integral'] ? true : false)
            ->setEnableFullReduce($setting['is_full_reduce'] ? true : false);
        return $this;
    }

    /**
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            //重新付款订单
            if ($this->id) {
                $order = GiftSendOrder::findOne([
                    'id' => $this->id,
                    'is_pay' => 0,
                    'is_delete' => 0,
                    'is_confirm' => 0,
                    'is_refund' => 0,
                ]);
                if (empty($order)) {
                    throw new OrderException('订单数据异常,无法支付');
                }
                (new GiftOrderPayForm)->getReturnData([$order]);
            }
            $data = $this->getAllData();
        } catch (OrderException $orderException) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $orderException->getMessage(),
                'error' => [
                    'line' => $orderException->getLine()
                ]
            ];
        }
        foreach ($data['mch_list'] as $mchItem) {
            if (isset($mchItem['city']) && isset($mchItem['city']['error'])) {
                return [
                    'code' => 1,
                    'msg' => $mchItem['city']['error']
                ];
            }
        }
        $setting = GiftSetting::search();

        $token = $this->getToken();
        $dataArr = [
            'mall' => \Yii::$app->mall,
            'user' => \Yii::$app->user->identity,
            'form_data' => $this->form_data,
            'token' => $token,
            'sign' => $this->sign,
            'supportPayTypes' => $this->supportPayTypes,
            'enableMemberPrice' => $this->getMemberPrice(),
            'enableFullReduce' => $this->getFullReduce(),
            'enableCoupon' => $this->getEnableCoupon(),
            'enableIntegral' => $this->getEnableIntegral(),
            'enableOrderForm' => $this->getEnableOrderForm(),
            'enablePriceEnable' => $this->getEnablePriceEnable(),
            'enableVipPrice' => $this->getEnableVipPrice(),
            'enableAddressEnable' => $this->getEnableAddressEnable(),
            'OrderSubmitFormClass' => static::class,
            'status' => $this->status,
            'appVersion' => \Yii::$app->appVersion,
            'auto_refund' => $setting['auto_refund'],
            'auto_remind' => $setting['auto_remind'],
            'type' => $this->type,
            'open_time' => $this->open_time,
            'open_num' => $this->open_num,
            'open_type' => $this->open_type,
            'bless_word' => $this->bless_word,
            'bless_music' => $this->bless_music,
        ];
        $class = new GiftOrderSubmitJob($dataArr);
        $queueId = \Yii::$app->queue->delay(0)->push($class);

        return [
            'code' => 0,
            'data' => [
                'token' => $token,
                'queue_id' => $queueId,
            ],
        ];
    }

    /**
     * 获取1个或多个订单的数据，按商户划分
     * @return array ['mch_list'=>'商户列表', 'total_price' => '多个订单的总金额（含运费）']
     * @throws OrderException
     * @throws \yii\db\Exception
     */
    public function getAllData()
    {
        $listData = $this->getMchListData($this->form_data['list']);
        foreach ($listData as &$mchItem) {
            $this->checkGoodsBuyLimit($mchItem['goods_list']);
            $formMchItem = $mchItem['form_data'];

            //礼物参数判断
            $msg = null;
            if (empty($formMchItem['type'])) {
                $msg = '缺少送礼物类型';
            }
            switch ($formMchItem['type']) {
                case 'num_open';
                    if (empty($formMchItem['open_num'])) {
                        $msg = '缺少开奖人数设置';
                    }
                    break;
                case 'time_open';
                    if (empty($formMchItem['open_time'])) {
                        $msg = '缺少开奖时间设置';
                    }
                    break;
            }
            if ($formMchItem['open_type'] === null || $formMchItem['open_type'] === '') {
                $msg = '送礼方式不能为空';
            }
            if (empty($formMchItem['bless_word'])) {
                $msg = '祝福语不能为空';
            }
            if (!empty($msg)) {
                throw new OrderException($msg);
            } else {
                $this->type = $formMchItem['type'];
                $this->open_time = $formMchItem['open_time'] ?? '0000-00-00 00:00:00';
                $this->open_num = $formMchItem['open_num'] ?? 0;
                $this->open_type = $formMchItem['open_type'];
                $this->bless_word = $formMchItem['bless_word'];
                $this->bless_music = $formMchItem['bless_music'];
            }

            $mchItem['pick_up_enable'] = true;
            $mchItem['show_delivery'] = false;
            $mchItem['show_remark'] = false;
            $mchItem['show_express_price'] = false;
            $mchItem['express_price'] = price_format(0);
            $totalGoodsPrice = 0;
            $totalGoodsOriginalPrice = 0;
            foreach ($mchItem['goods_list'] as $goodsItem) {
                $totalGoodsPrice += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }
            $mchItem['total_goods_price'] = price_format($totalGoodsPrice);
            $mchItem['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            $mchItem = $this->setMemberDiscountData($mchItem);
            $mchItem = $this->setFullReduceDiscountData($mchItem);
            $mchItem = $this->setCouponDiscountData($mchItem, $formMchItem);
            $mchItem = $this->setIntegralDiscountData($mchItem, $formMchItem);

            if ($mchItem['mch']['id'] == 0) {
                $mchItem = $this->setVipDiscountData($mchItem);
            }

            $totalPrice = price_format($mchItem['total_goods_price']);
            $mchItem['total_price'] = $totalPrice;
        }

        $total_price = 0;
        foreach ($listData as &$item) {
            $total_price += $item['total_price'];
        }

        return [
            'mch_list' => $listData,
            'total_price' => price_format($total_price),
            'price_enable' => true,
            'address' => null,
            'address_enable' => true,
            'has_ziti' => false,
            'custom_currency_all' => null,
            'allZiti' => false,
            'hasCity' => false,
            "show_address" => false,
        ];
    }

    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    protected function checkGoodsBuyLimit($goodsList)
    {
        $goodsIdMap = [];
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
            if (!empty($goods['goods_attr'])) {
                /** @var GoodsAttr $goodsAttr */
                $goodsAttr = $goods['goods_attr'];
                if ($goods['num'] > $goodsAttr->stock) {
                    throw new OrderException('商品库存不足: ' . $goods['name']);
                }
            }
            if (isset($goodsIdMap[$goods['id']])) {
                $goodsIdMap[$goods['id']]['num'] += $goods['num'];
            } else {
                $goodsIdMap[$goods['id']]['num'] = $goods['num'];
                $goodsIdMap[$goods['id']]['goods'] = $goods['goods_attr']['goods'];
            }
        }
        foreach ($goodsIdMap as $goodsId => $item) {
            /** @var Goods $goods */
            $goods = $item['goods'];
            if ($goods->confine_count <= 0) {
                continue;
            }

            $oldOrderGoodsNum = GiftSendOrderDetail::find()->alias('od')
                ->leftJoin(['o' => GiftSendOrder::tableName()], 'od.send_order_id=o.id')
                ->where([
                    'od.goods_id' => $goodsId,
                    'od.is_delete' => 0,
                    'o.user_id' => \Yii::$app->user->id,
                    'o.is_delete' => 0,
                ])
                ->sum('od.num');
            $oldOrderGoodsNum = $oldOrderGoodsNum ? intval($oldOrderGoodsNum) : 0;
            $totalNum = $oldOrderGoodsNum + $item['num'];
            if ($totalNum > $goods->confine_count) {
                throw new OrderException('商品购买数量超出限制: ' . $goods->name);
            }
        }
    }

    public function extraGoodsDetail($order, $goodsItem)
    {
        $orderDetail = new GiftSendOrderDetail();
        $orderDetail->send_order_id = $order->id;
        $orderDetail->goods_id = $goodsItem['id'];
        $orderDetail->goods_attr_id = $goodsItem['goods_attr']['id'];
        $orderDetail->num = $goodsItem['num'];
        $orderDetail->unit_price = $goodsItem['unit_price'];
        $orderDetail->total_original_price = $goodsItem['total_original_price'];
        $orderDetail->member_discount_price = $goodsItem['member_discount'];
        $orderDetail->total_price = $goodsItem['total_price'];
        $goodsInfo = [
            'attr_list' => $goodsItem['attr_list'],
            'goods_attr' => $goodsItem['goods_attr'],
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);

        if (!$orderDetail->save()) {
            throw new \Exception((new Model())->getErrorMsg($orderDetail));
        }
    }


    public function setVipDiscountData($mchItem)
    {
        $this->isTestUseVipCard = false;
        return parent::setVipDiscountData($mchItem);
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
