<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api\coupon;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\integral_mall\models\IntegralMallCoupons;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallCouponsUser;

class CouponOrderPayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var IntegralMallCouponsOrders $order */
            $order = IntegralMallCouponsOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            $order->is_pay = 1;
            $order->pay_time = date('Y-m-d H:i:s', time());
            switch ($paymentOrder->payType) {
                case PaymentOrder::PAY_TYPE_HUODAO:
                    $order->is_pay = 0;
                    $order->pay_type = 2;
                    break;
                case PaymentOrder::PAY_TYPE_BALANCE:
                    $order->pay_type = 3;
                    break;
                case PaymentOrder::PAY_TYPE_WECHAT:
                    $order->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_ALIPAY:
                    $order->pay_type = 1;
                    break;
                default:
                    break;
            }

            $user = User::findOne($order->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            // 订单支付完成后 将优惠券加入用户卡包
            $integralCoupons = IntegralMallCoupons::find()->where([
                'id' => $order->integral_mall_coupon_id,
                'mall_id' => $user->mall_id,
                'is_delete' => 0,
            ])->with('coupon')->asArray()->one();
            if (!$integralCoupons) {
                throw new \Exception('积分商城优惠券不存在');
            }

            if ($integralCoupons['coupon']['expire_type'] == 1) {
                $time = time() + $integralCoupons['coupon']['expire_day'] * 24 * 60 * 60;
                $startTime = date('Y-m-d H:i:s');
                $endTime = date('Y-m-d H:i:s', $time);
            } elseif ($integralCoupons['coupon']['expire_type'] == 2) {
                $startTime = $integralCoupons['coupon']['begin_time'];
                $endTime = $integralCoupons['coupon']['end_time'];
            } else {
                throw new \Exception('优惠券过期类型不存在:' . $integralCoupons['coupon']['expire_type']);
            }

            $userCoupon = new UserCoupon();
            $userCoupon->mall_id = $user->mall_id;
            $userCoupon->user_id = $user->id;
            $userCoupon->coupon_id = $integralCoupons['coupon']['id'];
            $userCoupon->sub_price = $integralCoupons['coupon']['sub_price'];
            $userCoupon->discount = $integralCoupons['coupon']['discount'];
            $userCoupon->coupon_min_price = $integralCoupons['coupon']['min_price'];
            $userCoupon->type = $integralCoupons['coupon']['type'];
            $userCoupon->start_time = $startTime;
            $userCoupon->end_time = $endTime;
            $userCoupon->is_use = 0;
            $userCoupon->receive_type = '积分商城兑换';
            $userCoupon->coupon_data = \Yii::$app->serializer->encode($integralCoupons['coupon']);
            $res = $userCoupon->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($userCoupon));
            }

            $order->user_coupon_id = $userCoupon->id;
            $res = $order->save();
            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $integralCouponUser = new IntegralMallCouponsUser();
            $integralCouponUser->user_id = $user->id;
            $integralCouponUser->mall_id = $user->mall_id;
            $integralCouponUser->integral_mall_coupon_id = $integralCoupons['id'];
            $integralCouponUser->user_coupon_id = $userCoupon->id;
            $res = $integralCouponUser->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($integralCouponUser));
            }

            \Yii::$app->currency->setUser($user)->integral->sub(
                $order->integral_num,
                "积分商城,兑换优惠券：{$order->integral_num}",
                $order->token
            );
            $transaction->commit();
        } catch (\Exception $e) {
            \Yii::error($e);
            $transaction->rollBack();
            throw $e;
        }
    }
}
