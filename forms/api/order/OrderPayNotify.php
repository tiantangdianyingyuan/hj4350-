<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 11:21
 */


namespace app\forms\api\order;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\events\OrderEvent;
use app\models\Model;
use app\models\Order;
use app\models\OrderVipCardInfo;
use app\plugins\vip_card\forms\common\CommonVip;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardDiscount;

class OrderPayNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        $order = Order::findOne([
            'order_no' => $paymentOrder->orderNo,
        ]);
        if (!$order) {
            return false;
        }
        $order->is_pay = 1;
        switch ($paymentOrder->payType) {
            case PaymentOrder::PAY_TYPE_HUODAO:
                $order->is_pay = 0;
                $order->pay_type = 2;
                break;
            case PaymentOrder::PAY_TYPE_BALANCE:
                $order->pay_type = 3;
                break;
            case PaymentOrder::PAY_TYPE_WECHAT:
                $order->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $order->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_BAIDU:
                $order->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_TOUTIAO:
                $order->pay_type = 1;
                break;
            default:
                break;
        }
        $order->pay_time = date('Y-m-d H:i:s');
        $this->setVipCardPrice($order, $paymentOrder);
        $order->save();

        $event = new OrderEvent();
        $event->order = $order;
        $event->sender = $this;
        \Yii::$app->trigger(Order::EVENT_PAYED, $event);
        return true;
    }

    /**
     * 下单同时购买超级会员卡时，订单价格调整，超级会员卡订单优惠信息记录
     * @param $order
     * @param $paymentOrder
     */
    protected function setVipCardPrice(&$order, $paymentOrder)
    {
        if (floatval($order->total_pay_price) === floatval($paymentOrder->amount)) {
            \Yii::info('setVipCardPrice--->支付金额与订单一致');
            return;
        }
        $orderVipCardInfo = OrderVipCardInfo::findOne(['order_id' => $order->id]);
        if (!$orderVipCardInfo) {
            \Yii::info('setVipCardPrice--->没有找到订单与超级会员卡同时下单记录');
            return;
        }
        if (floatval($orderVipCardInfo->order_total_price) !== floatval($paymentOrder->amount)) {
            \Yii::info('setVipCardPrice--->支付金额与超级会员卡记录金额不一致');
            return;
        }
        $discount = $order->total_pay_price - $paymentOrder->amount;
        \Yii::info('setVipCardPrice--->更新订单支付金额');
        $order->total_pay_price = price_format($paymentOrder->amount);

        $vipCardDetail = VipCardDetail::findOne($orderVipCardInfo->vip_card_detail_id);
        /** @var VipCard $vipCard */
        $vipCard = CommonVip::getCommon()->getMainCard($vipCardDetail->vip_id);

        $model = new VipCardDiscount();
        $model->discount = $discount;
        $model->discount_num = $vipCard->discount;
        $model->order_id = $order->id;
        $model->order_detail_id = $vipCardDetail->id;
        $model->main_id = $vipCard->id;
        $model->main_name = $vipCard->name;
        $model->detail_id = $vipCardDetail->id;
        $model->detail_name = $vipCardDetail->name;
        if (!$model->save()) {
            \Yii::error((new Model())->getErrorMsg($model));
        }
    }
}
