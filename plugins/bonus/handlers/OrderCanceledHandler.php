<?php


namespace app\plugins\bonus\handlers;

use app\plugins\bonus\events\OrderEvent;
use app\models\Order;
use app\handlers\HandlerBase;
use app\plugins\bonus\forms\mall\OrderBonusForm;


class OrderCanceledHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                /** @var OrderEvent $event */

                // 已付款就退款
                if ($event->order->is_pay == 1) {
                    //退款修改预分红状态
                    $form = new OrderBonusForm();
                    $form->order = $event->order;
                    $form->bonusDel();
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单取消分红事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
