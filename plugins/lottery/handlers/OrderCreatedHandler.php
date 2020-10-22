<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:31
 */


namespace app\plugins\lottery\handlers;

use app\handlers\HandlerBase;
use app\models\Order;
use app\plugins\lottery\models\LotteryLog;
use app\plugins\lottery\models\LotteryOrder;

class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            if ($event->order->sign != 'lottery') {
                return true;
            }

            if (!isset($event->sender->form_data['list'][0]['lottery_log_id'])) {
                return false;
            }

            $lottery_log_id = $event->sender->form_data['list'][0]['lottery_log_id'];

            $lotteryLog = lotteryLog::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'status' => 3,
                'id' => $lottery_log_id
            ]);
            if ($lotteryLog) {
                $t = \Yii::$app->db->beginTransaction();
                $lotteryLog->status = 4;
                $lotteryLog->save();
                $lotteryOrder = new LotteryOrder();
                $lotteryOrder->mall_id = \Yii::$app->mall->id;
                $lotteryOrder->lottery_log_id = $lotteryLog->id;
                $lotteryOrder->order_id = $event->order->id;
                if ($lotteryOrder->save()) {
                    $t->commit();
                } else {
                    $t->rollBack();
                }
            } else {
                \Yii::error('奖品已过期或不存在');
            }
        });
    }
}
