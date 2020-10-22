<?php

namespace app\plugins\community\models;


/**
 * Class Goods
 * @package app\plugins\community\models
 * @property CommunityGoods $communityGoods 抽奖券码数量
 */
class Order extends \app\models\Order
{
    public function getCommunityOrder()
    {
        return $this->hasOne(CommunityOrder::className(), ['order_id' => 'id']);
    }

    public function orderStatusText($order = null)
    {
        if (!$order) {
            $order = $this;
        }
        if (!$order) {
            throw new \Exception('order不能为空');
        }
        if (is_array($order)) {
            $order = (object)$order;
        }

        try {
            if ($order->cancel_status == 1) {
                return '已关闭';
            } elseif ($order->is_pay == 0 && $order->pay_type != 2) {
                return '待付款';
            } elseif ($order->is_send == 0) {
                return $order->send_type == 1 ? '待核销' : '待发货';
            } elseif ($order->is_send == 1 && $order->is_confirm == 0) {
                return $order->send_type == 1 ? '待核销' : '待提货';
            } elseif ($order->is_confirm == 1 && $order->is_sale == 0) {
                return $order->send_type == 1 ? '已核销' : '已提货';
            } elseif ($order->is_sale == 1) {
                return '已完成';
            } else {
                return '未知状态';
            }
        } catch (\Exception $exception) {
            return '未知状态';
        }
    }
}
