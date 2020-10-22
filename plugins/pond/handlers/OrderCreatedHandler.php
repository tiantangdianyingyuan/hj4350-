<?php

namespace app\plugins\pond\handlers;

use app\plugins\pond\models\PondLog;
use app\plugins\pond\models\PondOrder;
use app\models\Order;
use app\handlers\HandlerBase;

class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            if (!isset($event->sender->form_data['list'][0]['pond_id'])) {
                return false;
            }

            $pond_id = $event->sender->form_data['list'][0]['pond_id'];

            $pondLog = PondLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'status' => 0,
                'id' => $pond_id,
                'type' => 4,
            ]);
            if ($pondLog) {
                $pondLog->status = 1;
                $pondLog->raffled_at = date('Y-m-d H:i:s');
                $pondLog->save();
                $pondOrder = new PondOrder();
                $pondOrder->mall_id = \Yii::$app->mall->id;
                $pondOrder->pond_log_id = $pondLog->id;
                $pondOrder->order_id = $event->order->id;
                if (!$pondOrder->save()) {
                    \Yii::error('奖品日志保存失败');
                }
            } else {
                \Yii::error('奖品已过期或不存在');
            }
        });
    }
}
