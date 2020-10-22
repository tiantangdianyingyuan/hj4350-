<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/9
 * Time: 11:27
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\handlers;


use app\jobs\OrderCancelJob;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityCart;
use app\plugins\community\models\CommunityOrder;

class OrderCreatedHandlerClass extends \app\handlers\orderHandler\OrderCreatedHandlerClass
{
    public function deleteCartGoods()
    {
        CommunityCart::updateAll(['is_delete' => 1], ['id' => $this->event->cartIds]);
    }

    public function setAutoCancel()
    {
        $activity = CommunityOrder::find()->alias('co')->where(['co.order_id' => $this->event->order->id])
            ->leftJoin(['ca' => CommunityActivity::tableName()], 'ca.id = co.activity_id')
            ->select('ca.end_at')
            ->asArray()
            ->one();
        $orderAutoCancelMinute = empty($activity)
            ? \Yii::$app->mall->getMallSettingOne('over_time')
            : min(\Yii::$app->mall->getMallSettingOne('over_time') * 60, (strtotime($activity['end_at']) - time()));
        if (is_numeric($orderAutoCancelMinute) && $orderAutoCancelMinute > 0) {
            // 订单自动取消任务
            \Yii::$app->queue->delay($orderAutoCancelMinute)->push(new OrderCancelJob([
                'orderId' => $this->event->order->id,
            ]));
            $autoCancelTime = strtotime($this->event->order->created_at) + $orderAutoCancelMinute;
            $this->event->order->auto_cancel_time = mysql_timestamp($autoCancelTime);
            $this->event->order->save();
        }
        return $this;
    }
}
