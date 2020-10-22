<?php


namespace app\plugins\bonus\handlers;

use app\plugins\bonus\events\OrderRefundEvent;
use app\models\OrderRefund;
use app\handlers\HandlerBase;
use app\plugins\bonus\forms\mall\OrderBonusForm;


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
            $t = \Yii::$app->db->beginTransaction();
            try {
                //退货退款的扣除相应分红
                if ($event->order_refund->type == 1 && $event->order_refund->status == 2) {
                    //预分红
                    $form = new OrderBonusForm();
                    $form->order = $event->order_refund;
                    $form->bonusCut();
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单售后分红事件：');
                \Yii::error($exception);
                throw $exception;
            }

        });
    }
}
