<?php

namespace app\plugins\pick\handlers;

use app\handlers\orderHandler\BaseOrderCreatedHandler;
use app\jobs\OrderCancelJob;
use app\plugins\pick\models\PickActivity;
use app\plugins\pick\models\PickCart;
use app\plugins\pick\models\PickGoods;

class OrderCreatedEventHandler extends BaseOrderCreatedHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->setAutoCancel()->setShareUser()->setShareMoney()->receiptPrint('order')->deleteCartGoods();
    }

    protected function setAutoCancel()
    {
        $orderAutoCancelMinute = \Yii::$app->mall->getMallSettingOne('over_time');
        $detail = $this->event->order->detail;
        $activity = PickActivity::find()->alias('a')
            ->leftJoin(['g' => PickGoods::tableName()], 'g.pick_activity_id = a.id')
            ->andWhere(['a.is_delete' => 0, 'g.goods_id' => $detail[0]['goods_id']])
            ->one();
        $endTime = strtotime($activity->end_at) - time();//距离活动结束时间-秒
        $orderAutoCancel = $orderAutoCancelMinute * 60 < $endTime ? $orderAutoCancelMinute * 60 : $endTime;//取短的时间

        if (is_numeric($orderAutoCancel) && $orderAutoCancel > 0) {
            // 订单自动取消任务
            \Yii::$app->queue->delay($orderAutoCancel)->push(new OrderCancelJob([
                'orderId' => $this->event->order->id,
            ]));
            $autoCancelTime = strtotime($this->event->order->created_at) + $orderAutoCancel;
            $this->event->order->auto_cancel_time = mysql_timestamp($autoCancelTime);
            $this->event->order->save();
        }
        return $this;
    }

    protected function deleteCartGoods()
    {
        $res = PickCart::updateAll(['is_delete' => 1], ['id' => $this->event->cartIds]);
    }
}