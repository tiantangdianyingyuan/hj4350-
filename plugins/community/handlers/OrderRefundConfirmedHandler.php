<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\community\handlers;

use app\events\OrderRefundEvent;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\community\models\CommunityOrder;
use yii\db\Exception;

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
            $order = Order::findOne($event->order_refund->order_id);
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('community', $permission)) {
                \Yii::error('社区团购插件不存在');
                return;
            }
            if ($order->sign != 'community') {
                \Yii::error('非社区团购订单');
                return;
            }
            $t = \Yii::$app->db->beginTransaction();
            try {
                \Yii::error('社区团购订单售后事件开始：');

                $orderDetail = $event->order_refund->detail;
                $orderDetail->refund_status = 2;
                // 商家同意退款 减去相应利润
                if (($event->order_refund->type == 1 || $event->order_refund->type == 3) && $event->order_refund->status == 2) {
                    $goods_info = json_decode($orderDetail->goods_info, true);
                    $c_order = CommunityOrder::findOne(['order_id' => $event->order_refund->order_id, 'is_delete' => 0]);
                    $profit_data = json_decode($c_order->profit_data, true);
                    foreach ($profit_data as $profit_datum) {
                        if ($profit_datum['goods_id'] == $orderDetail->goods_id && $profit_datum['attr_id'] == $goods_info['goods_attr']['id']) {
                            $rate = bcdiv($event->order_refund->refund_price, bcmul($profit_datum['total_price'], $profit_datum['num'], 4), 4);
                            $refund_profit = $profit_datum['profit_price'] > 0 ? bcmul(bcmul($profit_datum['profit_price'], $profit_datum['num'], 4), $rate, 2) : 0;//减去售后商品退款比例利润
                            \Yii::error('rate:' . $rate . ' -- refund_profit:' . $refund_profit);
                            $c_order->profit_price -= $refund_profit;
                            if ($c_order->profit_price < 0) {
                                $c_order->profit_price = 0;
                            }
                        }
                    }
                    if (!$c_order->save()) {
                        throw new Exception((new Model())->getErrorMsg($c_order));
                    }
                }
                $t->commit();
            } catch (Exception $exception) {
                $t->rollBack();
                \Yii::error('社区团购订售后消事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
