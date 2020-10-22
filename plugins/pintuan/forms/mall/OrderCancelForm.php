<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\models\Model;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class OrderCancelForm extends Model
{
    public $order_id;
    public $pintuan_order_id;

    public function rules()
    {
        return [
            [['order_id', 'pintuan_order_id'], 'required'],
            [['order_id', 'pintuan_order_id'], 'integer'],
        ];
    }

    //订单取消退款
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var PintuanOrders $pintuanOrder */
            $pintuanOrder = PintuanOrders::find()
                ->andWhere(['id' => $this->pintuan_order_id, 'mall_id' => \Yii::$app->mall->id])
                ->with('orderRelation.order')
                ->one();

            if (!$pintuanOrder) {
                throw new \Exception('拼团订单不存在');
            }

            $sign = false;
            $isAllRefund = true;
            /** @var PintuanOrderRelation $item */
            foreach ($pintuanOrder->orderRelation as $item) {
                if ($item->order_id == $this->order_id) {
                    $sign = true;
                }

                /** @var PaymentOrder $paymentOrder */
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                
                if (!$paymentRefund && $item->order_id != $this->order_id) {
                    $isAllRefund = false;
                }
            }
            
            if (!$sign) {
                throw new \Exception('订单异常'); 
            }

            $order = Order::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->order_id,
                'is_delete' => 0,
                'is_send' => 0,
                'is_sale' => 0,
                'is_confirm' => 0
            ]);

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->cancel_status == 1) {
                throw new \Exception('订单已取消');
            }

            $order->words = '';
            $order->cancel_status = 1;
            $order->cancel_time = mysql_timestamp();
            $order->seller_remark = '拼团主动退款';
            $order->status = 1;

            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }

            \Yii::$app->trigger(Order::EVENT_CANCELED, new OrderEvent([
                'order' => $order
            ]));
            
            if ($isAllRefund) {
                $pintuanOrder->status = 3;
                $res = $pintuanOrder->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($pintuanOrder));
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '退款成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}