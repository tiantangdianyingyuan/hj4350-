<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\handlers;

use app\events\OrderEvent;
use app\events\OrderRefundEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\share\AddShareOrder;
use app\jobs\ChangeShareOrderJob;
use app\jobs\OrderSalesJob;
use app\models\CoreQueueData;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\ShareOrder;
use app\models\UserCard;
use yii\db\Exception;

class OrderRefundConfirmedHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            /** @var OrderRefundEvent $event */
            \Yii::$app->setMchId($event->order_refund->mch_id);
            $orderDetail = $event->order_refund->detail;
            $orderDetail->refund_status = 2;
            // 商家同意退款 销毁订单商品赠送的卡券
            if (in_array($event->order_refund->type, [1,3]) && $event->order_refund->status == 2) {
                $orderDetail->is_refund = 1;
                /* @var UserCard[] $userCards */
                $userCards = UserCard::find()->where([
                    'order_id' => $event->order_refund->order_id,
                    'order_detail_id' => $event->order_refund->order_detail_id
                ])->all();

                foreach ($userCards as $userCard) {
                    $userCard->is_delete = 1;
                    $userCard->card->updateCount('add', 1);
                    $res = $userCard->save();
                    if (!$res) {
                        \Yii::error('卡券销毁事件处理异常');
                    }
                }
                $price = $orderDetail->total_price - min($orderDetail->total_price, $event->order_refund->reality_refund_price);
                (new AddShareOrder())->refund($orderDetail, $price);
            }
            $orderDetail->save();

            // 判断queue队列中的售后是否已经触发
            $queueId = CoreQueueData::select($event->order_refund->order->token);
            if ($queueId && !\Yii::$app->queue->isDone($queueId)) {
                // 若未触发
                return;
            } else {
                // 若已触发，则重新添加
                $id = \Yii::$app->queue->delay(0)->push(new OrderSalesJob([
                    'orderId' => $event->order_refund->order_id
                ]));
                CoreQueueData::add($id, $event->order_refund->order->token);
            }
        });
    }
}
