<?php


namespace app\plugins\bonus\handlers;

use app\plugins\bonus\events\OrderEvent;
use app\models\Order;
use app\plugins\bonus\forms\mall\OrderBonusForm;
use yii\db\Exception;
use app\handlers\HandlerBase;


class OrderPayedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('bonus', $permission)) {
                return;
            }

            /** @var OrderEvent $event */
            //多商户不参与分红
            if ($event->order->mch_id > 0) {
                return;
            }
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                //预分红
                $form = new OrderBonusForm();
                $form->order = $event->order;
                $form->bonusAdd();
                $t->commit();
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('订单付款分红事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
