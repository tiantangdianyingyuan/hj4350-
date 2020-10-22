<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 11:02
 */

namespace app\plugins\advance\handlers;

use app\forms\common\template\tplmsg\Tplmsg;
use app\handlers\HandlerBase;
use app\plugins\advance\events\DepositEvent;
use app\plugins\advance\models\AdvanceOrder;

class DepositOrderRefundHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(AdvanceOrder::EVENT_REFUND, function ($event) {
            /** @var DepositEvent $event */
            $t = \Yii::$app->db->beginTransaction();
            try {
                //已支付的退定金
                if ($event->advanceOrder->is_pay == 1 && $event->advanceOrder->is_cancel == 1) {
                    \Yii::$app->payment->refund($event->advanceOrder->advance_no, $event->advanceOrder->deposit*$event->advanceOrder->goods_num);
                    $event->advanceOrder->is_refund = 1;
                    $event->advanceOrder->save();
                    $t->commit();
                    // 退款成功发送模版消息
                    $this->sendMsg($event->advanceOrder);
                } else {
                    return ;
                }
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单售后退定金事件：');
                \Yii::error($exception);
                throw $exception;
            }

        });
    }

    private function sendMsg($order)
    {
        $tplMsg = new Tplmsg();
        $tplMsg->depositOrderCancelMsg($order,null);
    }
}
