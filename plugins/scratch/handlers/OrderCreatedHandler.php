<?php

namespace app\plugins\scratch\handlers;

use app\plugins\scratch\models\ScratchLog;
use app\plugins\scratch\models\ScratchOrder;
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
            if (!isset($event->sender->form_data['list'][0]['scratch_id'])) {
                return false;
            }
            $scratch_id = $event->sender->form_data['list'][0]['scratch_id'];

            $scratchLog = ScratchLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'status' => 1,
                'id' => $scratch_id,
                'type' => 4,
            ]);
            if ($scratchLog) {
                $scratchLog->status = 2;
                $scratchLog->raffled_at = date('Y-m-d H:i:s');
                $scratchLog->save();
                $scratchOrder = new ScratchOrder();
                $scratchOrder->mall_id = \Yii::$app->mall->id;
                $scratchOrder->scratch_log_id = $scratchLog->id;
                $scratchOrder->order_id = $event->order->id;
                if (!$scratchOrder->save()) {
                    \Yii::error('奖品日志保存失败');
                }
            } else {
                \Yii::error('奖品已过期或不存在');
            }
        });
    }
}
