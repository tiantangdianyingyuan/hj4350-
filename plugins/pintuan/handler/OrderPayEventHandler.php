<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\handler;

use app\events\OrderEvent;
use app\forms\api\order\OrderException;
use app\handlers\orderHandler\OrderPayedHandlerClass;
use app\models\Order;
use app\models\OrderPayResult;
use app\models\User;
use app\plugins\pintuan\forms\common\PintuanSuccessForm;
use app\plugins\pintuan\jobs\PintuanCreatedOrderJob;
use app\plugins\pintuan\models\PintuanGoodsAttr;
use app\plugins\pintuan\models\PintuanOrderRelation;

class OrderPayEventHandler extends OrderPayedHandlerClass
{
    public function handle()
    {
        /** @var PintuanOrderRelation $pintuanOrderRelation */
        $pintuanOrderRelation = PintuanOrderRelation::find()->where(['order_id' => $this->event->order->id])->with('pintuanOrder')->one();
        if (!$pintuanOrderRelation) {
            throw new OrderException('拼团订单关联表不存在,订单ID:' . $this->event->order->id);
        }
        // 单独购买 不需要执行以下代码
        if ($pintuanOrderRelation->is_groups == 0) {
            \Yii::warning('拼团单独购买支付事件开始处理');
            $this->user = $this->event->order->user;
            if ($this->event->order->pay_type == 2) {
                if ($this->event->order->is_pay == 0) {
                    // 支付方式：货到付款未支付时，只触发部分通知类
                    parent::notice();
                } else {
                    // 支付方式：货到付款，订单支付时，触发剩余部分
                    parent::pay();
                }
            } else {
                parent::notice();
                parent::pay();
            }
            // 改价的情况 需重新计算分销价
            parent::addShareOrder();
        } else {
            \Yii::warning('拼团组支付事件开始处理');
            $this->user = $this->event->order->user;
            if ($this->event->order->pay_type == 2) {
                if ($this->event->order->is_pay == 0) {
                    // 支付方式：货到付款未支付时，只触发部分通知类
                    self::notice();
                } else {
                    // 支付方式：货到付款，订单支付时，触发剩余部分
                    self::pay();
                }
            } else {
                self::notice();
                self::pay();
            }
            self::addShareOrder();
        }
    }

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        if ($this->pintuanOrderRelation->is_groups == 0) {
            return parent::saveResult();
        }

        $userCouponList = $this->sendCoupon();
        $data = [
            'card_list' => [],
            'user_coupon_list' => $userCouponList,
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    protected function notice()
    {
        \Yii::error('--pintuan notice--');
        $this->sendTemplate();
        $this->sendBuyPrompt();
        $this->setGoods();
        $this->pintuan();
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--pintuan pay--');
        $this->saveResult();
        $this->becomeJuniorByFirstPay();
        $this->becomeShare();
        return $this;
    }

    protected function pintuan()
    {
        // 处理拼团组
        /**
         * @var PintuanOrderRelation $orderRelation ;
         */
        $orderRelation = PintuanOrderRelation::find()->where([
            'order_id' => $this->event->order->id,
        ])->with('pintuanOrder', 'order')->one();

        if (!$orderRelation) {
            \Yii::error('拼团订单支付事件,拼团关联有关系数据不存在');
            return $this;
        }

        // 待优化 更改状态操作应放到订单创建步骤中
        $this->event->order->status = 0;
        $res = $this->event->order->save();
        if (!$res) {
            \Yii::warning('拼团订单状态更新失败,订单号：' . $this->event->order->order_no);
        }

        if ($orderRelation->pintuanOrder->status == 0) {
            $orderRelation->pintuanOrder->status = 1;
            $res = $orderRelation->pintuanOrder->save();
            if (!$res) {
                \Yii::error($this->getErrorMsg($orderRelation->pintuanOrder));
                return $this;
            }

            // 支付完成 再开始执行拼团订单创建任务
            \Yii::$app->queue->delay($orderRelation->pintuanOrder->pintuan_time * 60 * 60)
                ->push(new PintuanCreatedOrderJob([
                    'pintuan_order_id' => $orderRelation->pintuanOrder->id,
                ]));
        }

        $pintuanSuccessForm = new PintuanSuccessForm();
        $pintuanSuccessForm->pintuanOrder = $orderRelation->pintuanOrder;
        $pintuanSuccessForm->updateOrder();

        // 如果同时有两个人拼团 会出现拼人数大于团总人数
        if ($pintuanSuccessForm->orderCount > $orderRelation->pintuanOrder->people_num) {
            \Yii::warning('拼团人数超出,开始执行退款操作');
            /** @var Order $order */
            $order = Order::find()->where(['id' => $this->event->order->id])->with('detail')->one();
            if (!$order) {
                \Yii::error('拼团组人数超出操作,订单号' . $this->event->order->id . '不存在');
            }

            $order->cancel_status = 1;
            $order->cancel_time = mysql_timestamp();
            $res = $order->save();
            if (!$res) {
                \Yii::error($this->getErrorMsg($order));
            }

            $orderRelation->cancel_status = 1;
            $res = $orderRelation->save();
            if (!$res) {
                \Yii::warning($this->getErrorMsg($orderRelation));
            }

            // 库存退回
            // 与商城不同
            foreach ($order->detail as $dItem) {
                $goodsInfo = \Yii::$app->serializer->decode($dItem->goods_info);
                $pintuanGoodsAttr = PintuanGoodsAttr::findOne([
                    'pintuan_goods_groups_id' => $orderRelation->pintuanOrder->pintuan_goods_groups_id,
                    'goods_id' => $dItem->goods_id,
                    'goods_attr_id' => $goodsInfo->goods_attr['id'],
                ]);

                $pintuanGoodsAttr->pintuan_stock += $dItem->num;
                if (!$pintuanGoodsAttr->save()) {
                    \Yii::error($this->getErrorMsg($pintuanGoodsAttr));
                    return $this;
                }
            }

            $event = new OrderEvent();
            $event->order = $order;
            \Yii::$app->trigger(Order::EVENT_CANCELED, $event);
        }
        return $this;
    }
}
