<?php

namespace app\plugins\bonus\handlers;


use app\plugins\bonus\events\OrderEvent;
use app\models\Order;
use app\handlers\HandlerBase;
use app\plugins\bonus\forms\mall\OrderBonusForm;


class OrderSalesHandler extends HandlerBase
{

    public function register()
    {
        \Yii::$app->on(Order::EVENT_SALES, function ($event) {
            /* @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                //分红完成
                $form = new OrderBonusForm();
                $form->order = $event->order;
                $form->bonusOver();
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单过售后分红事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
