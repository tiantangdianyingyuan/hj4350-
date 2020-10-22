<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\handlers;

use app\handlers\orderHandler\BaseOrderSalesHandler;
use app\plugins\scan_code_pay\Plugin;

class OrderSalesEventHandler extends BaseOrderSalesHandler
{
    protected function action()
    {
        if ($this->order->sign == (new Plugin())->getName()) {
            \Yii::warning('当面付售后事件开始');
            // 发放佣金
            $res = $this->giveShareMoney();
            // 发放积分
            $this->giveIntegral();
            // 消费升级会员等级
            $this->level();
            $this->giveBalance();
        }
    }

    protected function giveIntegral()
    {
        try {
            $sendData = $this->getActivitySend();

            if ($sendData['send_integral_num'] > 0) {
                \Yii::$app->currency->setUser($this->user)->integral->add((int) $sendData['send_integral_num'], '当面付赠送积分');
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error('当面付积分赠送失败' . $e->getMessage());
            return false;
        }
    }

    protected function giveBalance()
    {
        try {
            $sendData = $this->getActivitySend();

            if ($sendData['send_balance'] > 0) {
                \Yii::$app->currency->setUser($this->user)->balance->add((float) $sendData['send_balance'], '当面付赠送余额');
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error('当面付余额赠送失败' . $e->getMessage());
            return false;
        }
    }

    private function getActivitySend()
    {
        $detail = $this->event->order->detail[0];
        $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);

        return $goodsInfo['rules_data'];
    }
}
