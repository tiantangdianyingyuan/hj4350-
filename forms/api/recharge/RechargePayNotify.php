<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\recharge;


use app\core\payment\PaymentNotify;
use app\models\MallMembers;
use app\models\RechargeOrders;
use app\models\User;

class RechargePayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        try {
            /* @var RechargeOrders $order */
            $order = RechargeOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            if ($order->pay_type != 1) {
                throw new \Exception('必须使用微信支付');
            }

            $order->is_pay = 1;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $user = User::findOne($order->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $price = (float)($order->pay_price + $order->send_price);
            $desc = '充值余额：' . $order->pay_price . '元,赠送：' . $order->send_price . '元';

            //赠送会员
            if (!empty($order->send_member_id)) {
                $mallMembersModel = MallMembers::findOne([
                    'id' => $order->send_member_id,
                    'status' => 1,
                    'is_delete' => 0,
                ]);
                if ($mallMembersModel) {
                    if ($user->identity->member_level >= $mallMembersModel->level) {
                        $desc .= ',赠送会员失败：用户会员等级高于或等于赠送等级';
                    } else {
                        $desc .= sprintf(',赠送会员成功：会员ID=>%s', $mallMembersModel->id);
                        $user->identity->member_level = $mallMembersModel->level;
                        $user->identity->save();
                    }
                } else {
                    $desc .= ',赠送会员失败：会员状态异常，请查看会员是否启用';
                }
            }

            $customDesc = \Yii::$app->serializer->encode($order->attributes);
            \Yii::$app->currency->setUser($user)->balance->add($price, $desc, $customDesc, $order->order_no);
            \Yii::$app->currency->setUser($user)->integral->add(
                $order->send_integral,
                "余额充值,赠送积分{$order->send_integral}",
                $customDesc,
                $order->order_no
            );

        } catch (\Exception $e) {
            \Yii::error($e);
            throw $e;
        }
    }
}
