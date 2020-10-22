<?php

namespace app\forms\common\order\send;

use app\events\OrderSendEvent;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetailExpress;
use app\models\OrderDetailExpressRelation;

abstract class BaseSend extends Model
{
    public $order_id;
    public $express_id;
    public $mch_id;
    public $order_detail_id; // 订单物流分开发送
    public $is_trigger_event = true;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'mch_id', 'express_id'], 'integer'],
            [['order_detail_id'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单ID',
            'order_detail_id' => '发货商品',
        ];
    }

    abstract public function send();

    protected function getOrder()
    {
        $order = Order::findOne([
            'id' => $this->order_id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $this->mch_id ?: 0,
            'is_confirm' => 0,
            'is_sale' => 0,
        ]);

        if (!$order) {
            throw new \Exception('订单不存在');
        }

        if ($order->status == 0) {
            throw new \Exception('订单进行中,不能进行操作');
        }

        if ($order->is_pay == 0 && $order->pay_type != 2) {
            throw new \Exception('订单未支付');
        }

        if ($order->cancel_status == 2) {
            throw new \Exception('该订单正在申请取消操作，请先处理');
        }

        $this->checkOrder($order);

        return $order;
    }

    /**
     * @param Order $order
     * @return bool
     * 触发发货事件
     */
    public function triggerEvent($order)
    {
        if (!$this->is_trigger_event) {
            return true;
        }

        try {
            \Yii::$app->trigger(Order::EVENT_SENT, new OrderSendEvent(['order' => $order]));
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
        return true;
    }

    public function checkOrder($order)
    {
        // 兼容小程序端数据、小程序端不能传数组
        if (is_string($this->order_detail_id)) {
            try {
                $this->order_detail_id = json_decode($this->order_detail_id);
            } catch (\Exception $exception) {
                $this->order_detail_id = [];
            }
        }

        // 兼容小程序端多商户发货
        if ($this->mch_id > 0 && !$this->order_detail_id) {
            $orderDetailId =[];
            foreach ($order->detail as $key => $item) {
                $orderDetailId[] = $item->id;
            }
            $this->order_detail_id = $orderDetailId;
        }

        if (!is_array($this->order_detail_id)) {
            throw new \Exception('order_detail_id参数必须为数组');
        }

        if (count($this->order_detail_id) <= 0) {
            throw new \Exception('请勾选要发货的商品');
        }

        if (!$this->express_id) {
            if ($order->is_send == 1) {
                throw new \Exception('express_id参数异常');
            }

            $relation = $order->detailExpressRelation;
            if (count($relation) >= count($order->detail)) {
                throw new \Exception('订单物流数据异常');
            }

            // 同个商品不可重复发货
            $count = OrderDetailExpressRelation::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'order_detail_id' => $this->order_detail_id])->count();
            if ($count) {
                throw new \Exception('同一个商品不可重复发货');
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function saveOrderDetailExpress($order)
    {
        // 发货物流|编辑发货物流
        if ($this->express_id) {
            $orderDetailExpress = OrderDetailExpress::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->express_id,
            ])->one();

            if (!$orderDetailExpress) {
                throw new \Exception('订单物流不存在');
            }
        } else {
            $orderDetailExpress = new OrderDetailExpress();
            $orderDetailExpress->mall_id = \Yii::$app->mall->id;
            $orderDetailExpress->mch_id = \Yii::$app->user->identity->mch_id;
            $orderDetailExpress->order_id = $this->order_id;
        }

        $this->saveExtraData($orderDetailExpress);
        $res = $orderDetailExpress->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($orderDetailExpress));
        }

        $number = 0;
        if (!$this->express_id) {
            foreach ($this->order_detail_id as $detailId) {
                $model = new OrderDetailExpressRelation();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->user->identity->mch_id;
                $model->order_id = $this->order_id;
                $model->order_detail_id = $detailId;
                $model->order_detail_express_id = $orderDetailExpress->id;
                $res = $model->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($model));
                }
                $number++;
            }
        }

        // 到店自提订单 选择发货后不能再进行核销
        if ($order->send_type == 1) {
            $order->send_type = 0;
            $result = $order->save();
            if (!$result) {
                throw new \Exception($this->getErrorMsg($order));
            }
        }

        // 所有商品已发货，发货状态更新
        $relationCount = count($order->detailExpressRelation) + $number;
        if (count($order->detail) == $relationCount && $order->is_send == 0) {
            $order->is_send = 1;
            $order->send_time = mysql_timestamp();
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            //触发
            $this->triggerEvent($order);
        }
    }

    public function saveExtraData($orderDetailExpress)
    {
        return $orderDetailExpress;
    }
}
