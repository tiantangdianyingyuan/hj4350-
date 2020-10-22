<?php

namespace app\plugins\exchange\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\exchange\forms\common\CommonOrder;
use app\plugins\exchange\forms\common\CreateCode;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\models\ExchangeOrder;
use app\plugins\exchange\Plugin;

class OrderPayedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            /** @var OrderEvent $event */
            if ($event->order->sign !== (new Plugin())->getName()) {
                return true;
            }
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                /** @var OrderDetail $orderDetail */
                $orderDetail = OrderDetail::find()->where(['order_id' => $event->order->id])->with('goods')->one();
                if (!$orderDetail) {
                    throw new \Exception('订单详情不存在');
                }
                if ($orderDetail->goods->sign !== 'exchange') {
                    \Yii::error('兑换订单');
                    throw new \Exception('兑换订单');
                    //return true;
                }
                $exchangeGoods = ExchangeGoods::find()->where(['goods_id' => $orderDetail->goods_id])->one();
                // 创建礼品卡订单
                $libraryModel = $exchangeGoods->library;
                if (empty($libraryModel)) {
                    throw new \Exception('兑换库不合法');
                }

                $o = ExchangeOrder::find()->where([
                    'mall_id' => $event->order->mall_id,
                    'user_id' => $event->order->user_id,
                    'order_id' => $event->order->id,
                    'is_delete' => 0,
                ])->one();
                if ($o) {
                    throw new \Exception('订单重复触发');
                }

                //兑换码
                $create = new CreateCode($libraryModel, $event->order->mall_id);
                $code = $create->createOne();

                //礼品卡订单
                $order = new ExchangeOrder();
                $order->mall_id = $event->order->mall_id;
                $order->user_id = $event->order->user_id;
                $order->order_id = $event->order->id;
                $order->goods_id = $exchangeGoods->goods_id;
                $order->exchange_id = $libraryModel->id;
                $order->code_id = $code->id;
                $order->is_delete = 0;
                $order->save();
                (new CommonOrder())->autoSend($event->order);
                $transaction->commit();
            } catch (\Exception $e) {
                \Yii::error($e->getMessage());
                $transaction->rollBack();
                throw $e;
            }
        });
    }
}
