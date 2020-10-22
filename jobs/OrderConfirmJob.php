<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/14
 * Time: 15:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Order;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderConfirmJob extends Component implements JobInterface
{
    public $orderId;

    public function execute($queue)
    {
        \Yii::error('order confirm job ->>' . $this->orderId);
        $order = Order::findOne([
            'id' => $this->orderId,
            'is_delete' => 0,
            'is_send' => 1,
            'is_confirm' => 0
        ]);
        if (!$order) {
            return true;
        }
        $mall = Mall::findOne(['id' => $order->mall_id]);
        \Yii::$app->setMall($mall);
        if ($order->pay_type == 2) {
            \Yii::error('货到付款的无法自动收货');
            return true;
        }

        // TODO 订单处于售后状态是未处理

        $order->is_confirm = 1;
        $order->confirm_time = mysql_timestamp();
        if ($order->save()) {
            $event = new OrderEvent([
                'order' => $order,
            ]);
            \Yii::$app->trigger(Order::EVENT_CONFIRMED, $event);
        }
    }
}
