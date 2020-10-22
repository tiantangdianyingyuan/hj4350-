<?php

namespace app\plugins\community\handlers;


use app\events\OrderEvent;
use app\models\Model;
use app\models\Order;
use app\handlers\HandlerBase;
use app\plugins\community\models\CommunityBonusLog;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityOrder;
use yii\db\Exception;


class OrderSalesHandler extends HandlerBase
{

    public function register()
    {
        \Yii::$app->on(Order::EVENT_SALES, function ($event) {
            /* @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            if ($event->order->sign != 'community') {
                \Yii::error('非社区团购订单，不作团长分红');
                return;
            }
            \Yii::error('社区团购订单利润记录开始:');

            $t = \Yii::$app->db->beginTransaction();
            try {
                $order_info = CommunityOrder::findOne(['order_id' => $event->order->id, 'is_delete' => 0]);
                if (empty($order_info)) {
                    throw new Exception('订单不存在利润记录，ID-' . $event->order->id);
                }
                //利润流水记录
                $bonus_log = new CommunityBonusLog();
                $bonus_log->mall_id = $event->order->mall_id;
                $bonus_log->user_id = $order_info->middleman_id;
                $bonus_log->order_id = $event->order->id;
                $bonus_log->activity_id = $order_info->activity_id;
                $bonus_log->desc = '团员订单利润';
                $bonus_log->price = $event->order->total_pay_price;
                $bonus_log->profit_price = $order_info->profit_price > 0 ? $order_info->profit_price : 0;
                if (!$bonus_log->save()) {
                    throw new \Exception((new Model())->getErrorMsg($bonus_log));
                }
                //团长利润记录
                if (CommunityMiddleman::updateAllCounters(['total_price' => $event->order->total_pay_price, 'money' => $bonus_log->profit_price, 'total_money' => $bonus_log->profit_price, 'order_count' => 1],
                        ['user_id' => $order_info->middleman_id, 'is_delete' => 0]) <= 0) {
                    throw new Exception('利润存入失败');
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单过售后利润事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
