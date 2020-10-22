<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\mall_member;


use app\core\payment\PaymentNotify;
use app\models\MallMemberOrders;
use app\models\UserIdentity;

class MallMemberPayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $order = MallMemberOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            $order->is_pay = 1;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception('订单支付状态更新失败');
            }

            $userIdentity = UserIdentity::find()->where(['user_id' => $order->user_id])->one();

            if (!$userIdentity) {
                throw new \Exception('用户角色记录不存在,会员购买订单号:' . $order->order_no);
            }
            $userIdentity->member_level = \Yii::$app->serializer->decode($order->detail)['after_level'];
            $res = $userIdentity->save();

            if (!$res) {
                throw new \Exception('用户会员等级更新失败,会员购买订单号:' . $order->order_no);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
