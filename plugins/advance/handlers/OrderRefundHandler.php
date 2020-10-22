<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/9/30
 * Time: 15:02
 */

namespace app\plugins\advance\handlers;

use app\forms\common\template\tplmsg\Tplmsg;
use app\models\OrderRefund;
use app\handlers\HandlerBase;
use app\events\OrderRefundEvent;
use app\plugins\advance\models\AdvanceOrder;

class OrderRefundHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            /** @var OrderRefundEvent $event */
            //权限判断
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('advance', $permission)) {
                \Yii::error('预售插件不存在');
                return;
            }
            if ($event->order_refund->order->sign != 'advance') {
                \Yii::error('非预售订单');
                return;
            }

            try{
                if (empty($event->advance_refund)) {
                    \Yii::error('定金退款金额无');
                    return;
                }
                if ($event->order_refund->type != 1 || $event->order_refund->status != 2 || $event->advance_refund <= 0) {
                    \Yii::error('售后订单状态不符合，不退定金');
                    return;
                }
            }catch (\Exception $exception) {
                \Yii::error('订单售后退定金事件：');
                \Yii::error($exception);
                throw $exception;
            }

            // 事务要有始有终
            $t = \Yii::$app->db->beginTransaction();
            try {
                $advance_order = AdvanceOrder::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'order_id' => $event->order_refund->order_id,
                    'is_pay' => 1,
                    'is_cancel' => 0,
                    'is_refund' => 0,
                    'is_delete' => 0
                ])->with('user.userInfo')->one();
                if (empty($advance_order)) {
                    throw new \Exception('定金订单不存在');
                }
                /* @var \app\plugins\advance\models\AdvanceOrder $advance_order */
                if ($event->advance_refund > ($advance_order->deposit * $advance_order->goods_num)) {
                    throw new \Exception('退款定金金额大于定金金额');
                }
//                $advance_order->is_cancel = 1;
                $advance_order->is_refund = 1;
                if (!$advance_order->save()) {
                    throw new \Exception($advance_order->errors[0]);
                }
                //已支付的退定金
                if ($advance_order->is_pay == 1 && $event->advance_refund > 0) {
                    \Yii::error('预售退款涉及到定金 售后退定金部分');
                    \Yii::$app->payment->refund($advance_order->advance_no, $event->advance_refund);
                    // 退款成功发送模版消息
                    $this->sendMsg($advance_order, $event->advance_refund);
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单售后退定金事件：');
                \Yii::error($exception);
                throw $exception;
            }

        });
    }

    private function sendMsg($order, $price)
    {
        $tplMsg = new Tplmsg();
        $tplMsg->depositOrderCancelMsg($order, $price);
    }
}
