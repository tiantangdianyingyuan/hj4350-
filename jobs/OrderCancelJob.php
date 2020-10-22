<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/14 9:51
 */


namespace app\jobs;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class OrderCancelJob extends BaseObject implements JobInterface
{
    public $orderId;

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $order = Order::findOne([
            'id' => $this->orderId,
            'is_pay' => 0,
            'pay_type' => 0,
            'is_delete' => 0,
        ]);
        if (!$order) {
            return;
        }
        if ($order->cancel_status == 1) {
            return ;
        }
        \Yii::warning('----订单自动取消----');
        $mall = Mall::findOne(['id' => $order->mall_id]);
        \Yii::$app->setMall($mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order->cancel_status = 1;
            $order->cancel_time = mysql_timestamp();
            if ($order->save()) {
                $event = new OrderEvent([
                    'order' => $order,
                ]);
                \Yii::$app->trigger(Order::EVENT_CANCELED, $event);
                $t->commit();
            } else {
                throw new \Exception((new Model())->getErrorMsg($order));
            }
        } catch (\Exception $exception) {
            $t->rollBack();
        }
    }
}
