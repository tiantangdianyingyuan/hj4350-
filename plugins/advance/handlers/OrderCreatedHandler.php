<?php


namespace app\plugins\advance\handlers;

use app\plugins\advance\events\OrderEvent;
use app\models\Order;
use app\plugins\advance\models\AdvanceOrder;
use yii\db\Exception;
use app\handlers\HandlerBase;


class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('advance', $permission)) {
                \Yii::error('预售插件不存在');
                return;
            }
            if ($event->order->sign != 'advance') {
                \Yii::error('非预售订单');
                return;
            }
            \Yii::error('订单付款预售尾款回调开始：');
            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                $advance_model = AdvanceOrder::findOne(['order_token' => $event->order->token]);

                if (empty($advance_model)) {
                    throw new Exception('预售定金订单不存在——订单token：' . $event->order->token);
                }
                $advance_model->order_id = $event->order->id;
                $advance_model->order_no = $event->order->order_no;
                if (!$advance_model->save()) {
                    throw new Exception(json_encode($advance_model->errors));
                }
                $t->commit();
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('订单付款预售尾款事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
