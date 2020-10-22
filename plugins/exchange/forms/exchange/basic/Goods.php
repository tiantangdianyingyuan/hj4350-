<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;

use app\core\payment\PaymentOrder;
use app\events\OrderEvent;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\api\order\OrderPayNotify;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\exchange\forms\common\CommonOrder;
use app\plugins\exchange\models\ExchangeRecordOrder;

class Goods extends BaseAbstract implements Base
{
    public $has_imitate;

    public function exchange(&$message)
    {
        try {
            $origin = $this->extra_info['origin'] ?? false;
            if ($origin && $origin === 'admin') {
                $this->has_imitate = $this->extra_info['has_imitate'] ?? false;
                //Create 订单
                $order = $this->createOrder();
                //Create 详情
                $this->createOrderDetail($order);
                //Create 插件订单
                $this->createExchangeOrder($order);
                //handle 订单创建
                $this->handleCreateEvent($order);
                //handle 自动完成
                (new CommonOrder())->autoSend($order);
                //create PaymentOrder 支付完成
                $this->paymentOrder($order);
                return true;
            }
            //支付
            if ($origin && $origin === 'alipay') {
                return true;
            }
            //兑换
            return false;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }

    public function paymentOrder($order)
    {
        $payOrder = new PaymentOrder([
            'title' => '兑换中心后台',
            'amount' => floatval($order->total_pay_price),
            'orderNo' => $order->order_no,
            'notifyClass' => OrderPayNotify::class,
            'supportPayTypes' => [\app\core\payment\Payment::PAY_TYPE_BALANCE],
        ]);
        $copy = new CopyPayment();
        $id = $copy->createOrder($payOrder, $this->user);
        return $copy->payBuyBalance($id);
    }

    public function createOrder(): Order
    {
        $order = new Order();
        $order->name = $this->extra_info['name'] ?? $this->user->nickname;
        $order->mobile = $this->extra_info['mobile'] ?? $this->user->mobile;
        $order->address = '';
        $order->mall_id = $this->user->mall_id;
        $order->user_id = $this->user->id;
        $order->order_no = Order::getOrderNo('');
        $order->total_price = 0;
        $order->total_pay_price = 0;
        $order->express_original_price = 0;
        $order->express_price = 0;
        $order->total_goods_price = 0;
        $order->total_goods_original_price = 0;

        $order->member_discount_price = 0;
        $order->use_user_coupon_id = 0;
        $order->coupon_discount_price = 0;
        $order->use_integral_num = 0;
        $order->integral_deduction_price = 0;
        $order->remark = '';
        $order->order_form = \Yii::$app->serializer->encode([]);
        $order->words = '';

        $order->is_pay = 1;
        $order->pay_type = 3;// 支付方式：1.在线支付 2.货到付款 3.余额支付
        $order->send_type = 3; //配送方式：0--快递配送 1--到店自提 2--同城配送 3--自动

        $order->is_sale = 1;
        $order->auto_sales_time = mysql_timestamp();
        $order->is_confirm = 1;
        $order->confirm_time = mysql_timestamp();
        $order->is_send = 1;
        $order->send_time = mysql_timestamp();

        $order->support_pay_types = \Yii::$app->serializer->encode([]);

        $order->sign = 'exchange';
        $order->token = \Yii::$app->security->generateRandomString();
        $order->status = 1;
        if (!$order->save()) {
            throw new \Exception('order ERROR');
        }
        return $order;
    }

    public function createOrderDetail($order)
    {
        $attr_id = $this->config['attr_id'];
        $goods = \app\plugins\exchange\models\Goods::findOne($this->config['goods_id']);

        $orderDetail = new OrderDetail();
        $orderDetail->order_id = $order->id;
        $orderDetail->goods_id = $goods->id;
        $orderDetail->num = intval($this->config['goods_num']);
        $orderDetail->unit_price = 0;
        $orderDetail->total_original_price = 0;
        $orderDetail->total_price = 0;
        $orderDetail->member_discount_price = 0;
        $orderDetail->sign = 'exchange';

        $attrModel = GoodsAttr::findOne($attr_id);
        $attrGroups = \Yii::$app->serializer->decode($goods->attr_groups);
        $orderGoodsAttr = new OrderGoodsAttr();
        $orderGoodsAttr->goods = $goods;
        $orderGoodsAttr->goodsAttr = $attrModel;
        //$orderGoodsAttr->goodsAttr = $goods->attr[0];
        $orderGoodsAttr->integral_price = 0;
        $orderGoodsAttr->use_integral = 0;
        //sub Stork

        (new GoodsAttr())->updateStock($orderDetail->num, 'sub', $orderGoodsAttr->id);

        $goodsInfo = [
            'attr_list' => (new \app\models\Goods())->signToAttr($attrModel['sign_id'], $attrGroups),
            'goods_attr' => $orderGoodsAttr,
            'extra_info' => [
                'code_id' => $this->codeModel->code,
                'config' => $this->config,
            ]
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);
        if (!$orderDetail->save()) {
            throw new \Exception('orderDetail ERROR');
        }
        return $orderDetail;
    }

    public function createExchangeOrder($order)
    {
        $exchangeOrder = new ExchangeRecordOrder();
        $exchangeOrder->mall_id = $this->user->mall_id;
        $exchangeOrder->user_id = $this->user->id;
        $exchangeOrder->order_id = $order->id;
        $exchangeOrder->code_id = $this->codeModel->id;
        $exchangeOrder->token = $this->config['token'];
        if (!$exchangeOrder->save()) {
            throw new \Exception('exchangeOrder ERROR');
        }
    }

    public function handleCreateEvent($order)
    {
        $user = $this->user;
        $sender = new class ($user) {
            public $user;

            public function __construct($user)
            {
                $this->user = $user;
            }
        };
        $event = new OrderEvent();
        $event->order = $order;
        $event->sender = $sender;
        \Yii::$app->trigger(Order::EVENT_CREATED, $event);
    }
}
