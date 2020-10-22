<?php


namespace app\plugins\advance\handlers;

use app\forms\common\template\tplmsg\Tplmsg;
use app\plugins\advance\events\OrderEvent;
use app\models\Order;
use app\plugins\advance\models\AdvanceOrder;
use yii\db\Exception;
use app\handlers\HandlerBase;


class OrderCanceledHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
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
            \Yii::error('订单付款预售尾款取消回调开始：');
            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $t = \Yii::$app->db->beginTransaction();
            try {
                $refund = false;
                $advance_model = AdvanceOrder::findOne(['order_id' => $event->order->id]);

                if (empty($advance_model)) {
                    throw new Exception('预售定金订单不存在——订单ID：' . $event->order->id);
                }

                // 已付款就退款 加上货到付款
                if ($event->order->is_pay == 1 || ($event->order->pay_type == 2 && $event->order->is_pay == 0)) {
                    \Yii::$app->payment->refund($advance_model->advance_no, $advance_model->deposit * $advance_model->goods_num);
//                    $advance_model->is_cancel = 1;
                    $advance_model->is_refund = 1;
                    $refund = true;
                } elseif ($event->order->is_pay == 0) {
                    $advance_model->order_id = 0;
                    $advance_model->order_no = '0';
                    //未付款尾款订单软删除处理
                    Order::updateAll(['is_delete' => 1], ['id' => $event->order->id]);
                }

                if (!$advance_model->save()) {
                    throw new Exception(json_encode($advance_model->errors));
                }

                $t->commit();
                // 退款成功发送模版消息
                if ($refund) {
                    $this->sendMsg($advance_model);
                }
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('订单付款预售尾款取消事件：');
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
