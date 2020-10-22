<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/18 17:00
 */


namespace app\forms\api\order;


use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderVipCardInfo;

abstract class OrderPayFormBase extends Model
{
    abstract public function getResponseData();


    /**
     * @param Order[] $orders
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    protected function getReturnData($orders)
    {
        $hasMchOrder = false;
        foreach ($orders as $order) {
            if ($order->mch_id != 0) {
                $hasMchOrder = true;
                break;
            }
        }
        $hasVipCardOrder = false; //有超级会员卡
        foreach ($orders as $order) {
            if ($order->sign === 'vip_card') {
                $hasVipCardOrder = true;
                break;
            }
        }
        $hasNoPluginGoods = false; //有非插件商品
        foreach ($orders as $order) {
            if ($order->sign === '' || $order->sign === null) {
                $hasNoPluginGoods = true;
                break;
            }
        }
        if ($hasVipCardOrder && $hasNoPluginGoods) {
            foreach ($orders as $order) {
                if ($order->sign === '' || $order->sign === null) {
                    break;
                }
            }
        }

        $supportPayTypes = (array)$order->decodeSupportPayTypes($order->support_pay_types);
        if (!count($supportPayTypes)) {
            $supportPayTypes = [
                Payment::PAY_TYPE_BALANCE,
                Payment::PAY_TYPE_WECHAT,
                Payment::PAY_TYPE_ALIPAY,
            ];
        }
        if (($hasMchOrder || $hasVipCardOrder) && isset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO])) {
            unset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO]);
        }
        if (($hasMchOrder || $hasVipCardOrder) && in_array(PaymentOrder::PAY_TYPE_HUODAO, $supportPayTypes)) {
            foreach ($supportPayTypes as $i => &$item) {
                if ($item === PaymentOrder::PAY_TYPE_HUODAO) {
                    unset($supportPayTypes[$i]);
                    break;
                }
            }
        }
        $paymentOrders = [];
        foreach ($orders as $order) {
            $totalPayPrice = $order->total_pay_price;
            if ($hasVipCardOrder) {
                $orderVipCardInfo = OrderVipCardInfo::findOne([
                    'order_id' => $order->id,
                ]);
                if ($orderVipCardInfo) {
                    $totalPayPrice = $orderVipCardInfo->order_total_price;
                }
            }
            $paymentOrder = new PaymentOrder([
                'title' => $this->getOrderTitle($order),
                'amount' => (float)$totalPayPrice,
                'orderNo' => $order->order_no,
                'notifyClass' => OrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $paymentOrders[] = $paymentOrder;
        }
        $id = \Yii::$app->payment->createOrder($paymentOrders);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @param Order $order
     */
    private function getOrderTitle($order)
    {
        /** @var OrderDetail[] $details */
        $details = $order->getDetail()->andWhere(['is_delete' => 0])->with('goods')->all();
        if (!$details || !is_array($details) || !count($details)) {
            return $order->order_no;
        }
        $titles = [];
        foreach ($details as $detail) {
            if (!$detail->goods) {
                continue;
            }
            $titles[] = $detail->goods->name;
        }
        $title = implode(';', $titles);
        if (mb_strlen($title) > 32) {
            return mb_substr($title, 0, 32);
        } else {
            return $title;
        }
    }
}
