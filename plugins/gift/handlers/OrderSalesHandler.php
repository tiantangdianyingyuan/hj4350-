<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/22
 * Time: 16:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\gift\handlers;


use app\handlers\orderHandler\BaseOrderSalesHandler;
use app\models\OrderDetail;
use app\models\ShareOrder;
use app\models\User;
use app\models\Order;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftUserOrder;
use yii\db\Exception;

class OrderSalesHandler extends BaseOrderSalesHandler
{
    /* @var OrderDetail $orderDetail */
    public $orderDetail;

    /* @var GiftUserOrder $giftUserOrder */
    public $giftUserOrder;

    public function handle()
    {
        $giftOrder = GiftOrder::findOne(['order_id' => $this->event->order->id]);
        \Yii::error($this->event->order);
        if (!$giftOrder) {
            \Yii::error('礼物订单没找到');
            return false;
        }
        $this->giftUserOrder = GiftUserOrder::findOne($giftOrder->user_order_id);
        if (!$this->giftUserOrder) {
            \Yii::error('礼物参与订单未找到');
            return false;
        }
        $this->orderDetail = OrderDetail::findOne(['id' => $giftOrder->buy_order_detail_id, 'is_refund' => 0]);
        if (!$this->orderDetail) {
            \Yii::error('礼物商城订单详情没找到或已退款');
            return false;
        }
        $this->order = Order::findOne(['id' => $this->orderDetail->order_id]);
        $this->user = $this->order->user;
        $this->action();
    }

    protected function action()
    {
        // 发放佣金
        $this->giveShareMoney();
        // 发放积分
        $this->giveIntegral();
        // 发放余额
        $this->giveBalance();
        // 消费升级会员等级
        $this->level();
        //礼物商城订单售后状态更改
        $this->sale();
    }

    protected function giveShareMoney()
    {
        try {
            \Yii::warning('发放佣金');
            $shareOrder = ShareOrder::findOne([
                'mall_id' => $this->order->mall_id, 'is_delete' => 0, 'is_transfer' => 0,
                'order_detail_id' => $this->orderDetail->id
            ]);
            $list = [
                'first_parent_id' => $shareOrder->first_parent_id,
                'first_price' => $shareOrder->first_price,
                'second_parent_id' => $shareOrder->second_parent_id,
                'second_price' => $shareOrder->second_price,
                'third_parent_id' => $shareOrder->third_parent_id,
                'third_price' => $shareOrder->third_price,
            ];
            if ($list['first_parent_id'] > 0) {
                $first = User::findOne($list['first_parent_id']);
                \Yii::$app->currency->setUser($first)->brokerage
                    ->add(floatval($list['first_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            if ($list['second_parent_id'] > 0) {
                $second = User::findOne($list['second_parent_id']);
                \Yii::$app->currency->setUser($second)->brokerage
                    ->add(floatval($list['second_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            if ($list['third_parent_id'] > 0) {
                $third = User::findOne($list['third_parent_id']);
                \Yii::$app->currency->setUser($third)->brokerage
                    ->add(floatval($list['third_price']), "由订单{$this->order->order_no}提供的分销佣金");
            }
            ShareOrder::updateAll(['is_transfer' => 1], [
                'mall_id' => $this->order->mall_id, 'order_detail_id' => $this->orderDetail->id, 'is_delete' => 0
            ]);
            return $list;
        } catch (\Exception $e) {
            \Yii::error($e);
            return false;
        }
    }

    protected function giveIntegral()
    {
        try {
            $integral = 0;
            $sends = [];
            if ($this->orderDetail->goods->give_integral_type == 1) {
                $sendIntegral = ($this->orderDetail->goods->give_integral * $this->orderDetail->num);
            } else {
                $sendIntegral = (intval($this->orderDetail->goods->give_integral * $this->orderDetail->total_price / 100));
            }
            if ($sendIntegral > 0) {
                $integral += $sendIntegral;
                $sends[] = [
                    'goods_id' => $this->orderDetail->goods->id,
                    'goods_name' => $this->orderDetail->goods->name,
                    'send_num' => $sendIntegral
                ];
            }
            if ($integral > 0) {
                $customDesc = \Yii::$app->serializer->encode([$this->order->attributes, 'sends' => $sends]);
                \Yii::$app->currency->setUser($this->user)->integral->add($integral, '订单购买赠送积分', $customDesc, $this->order->order_no);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // 余额发放
    protected function giveBalance()
    {
        try {
            $balance = 0;
            $sends = [];
            if ($this->orderDetail->goods->give_balance_type == 1) {
                $sendBalance = ($this->orderDetail->goods->give_balance * $this->orderDetail->num);
            } else {
                $sendBalance = ($this->orderDetail->goods->give_balance * $this->orderDetail->total_price / 100);
            }
            if ($sendBalance > 0) {
                $balance += $sendBalance;
                $sends[] = [
                    'goods_id' => $this->orderDetail->goods->id,
                    'goods_name' => $this->orderDetail->goods->name,
                    'send_num' => $sendBalance
                ];
            }
            if ($balance > 0) {
                $customDesc = \Yii::$app->serializer->encode([$this->order->attributes, 'sends' => $sends]);
                \Yii::$app->currency->setUser($this->user)->balance->add(price_format($balance, 'float'), '购买商品赠送', $customDesc, $this->order->order_no);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function sale()
    {
        return CommonGift::giftOver($this->giftUserOrder->gift_id);
    }
}
