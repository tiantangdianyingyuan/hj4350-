<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/15 15:55
 */


namespace app\forms\api\order;


use app\forms\api\share\ShareApplyForm;
use app\models\AdminInfo;
use app\models\Model;
use app\models\Order;
use app\models\OrderPayResult;

class OrderPayResultForm extends Model
{
    public $payment_order_union_id;

    public function rules()
    {
        return [
            [['payment_order_union_id'], 'required'],
            [['payment_order_union_id'], 'integer'],
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        $paymentOrderUnion = \Yii::$app->payment->getPaymentOrderUnion([
            'id' => $this->payment_order_union_id,
            'user_id' => \Yii::$app->user->id,
        ]);
        if (!$paymentOrderUnion) {
            return [
                'code' => 1,
                'msg' => '无效的payment_order_union_id。'
            ];
        }
        $paymentOrders = \Yii::$app->payment->getPaymentOrders($this->payment_order_union_id);
        $cardList = [];
        $userCouponList = [];
        $sendData = [];
        $orderList = [];
        $orderPageUrl = null;

        foreach ($paymentOrders as $paymentOrder) {
            $order = Order::findOne([
                'order_no' => $paymentOrder->order_no,
            ]);
            if ($order && $order->sign === 'pintuan' && $order->status == 0) {
                $orderPageUrl = "/plugins/pt/order/order";
                $por = \app\plugins\pintuan\models\PintuanOrderRelation::findOne([
                    'order_id' => $order->id,
                    'is_delete' => 0,
                ]);
                if ($por) {
                    $orderPageUrl = "/plugins/pt/detail/detail?pintuan_order_id={$por->pintuan_order_id}&id={$por->pintuan_order_id}";
                }
            }
            if ($order && $order->sign === 'exchange') {
                //区分不同订单
                $sign = current($order['detail'])['goods']['sign'];
                if ($sign === 'exchange') {
                    $orderPageUrl = "/plugins/exchange/list/list?tab=1";
                }
            }
            $orderPayResult = OrderPayResult::findOne(['order_id' => $order->id,]);
            if (!$orderPayResult) {
                continue;
            }
            $data = $orderPayResult->decodeData($orderPayResult->data);
            $cardList = array_merge($cardList, $data['card_list']);
            $userCouponList = array_merge($userCouponList, $data['user_coupon_list']);

            $orderList[] = [
                'id' => $order->id,
                'sign' => $order->sign,
            ];

            // TODO 此代码应写在插件上
            if (isset($data['send_data'])) {
                $sendData = $data['send_data'];
            }
        }

        // 校验用户是否满足申请分销商
        $shareCheck = false;
        try {
            $isShareTip = \Yii::$app->mall->getMallSettingOne('is_share_tip');
            if (\Yii::$app->user->identity->identity->is_distributor != 1 && $isShareTip == 1) {
                $shareApplyForm = new ShareApplyForm();
                $shareApplyForm->mall = \Yii::$app->mall;
                $shareCheck = $shareApplyForm->checkApply();
            }
        } catch (\Exception $exception) {
        }


        return [
            'code' => 0,
            'data' => [
                'total_pay_price' => price_format($paymentOrderUnion->amount),
                'card_list' => $cardList,
                'user_coupon_list' => $userCouponList,
                'send_data' => $sendData,
                'goods_list' => [],
                'plugins' => \Yii::$app->branch->childPermission(AdminInfo::findOne(['user_id' => \Yii::$app->mall->user_id])),
                'order_list' => $orderList,
                'order_page_url' => $orderPageUrl,
                'shareCheck' => $shareCheck
            ],
        ];
    }
}
