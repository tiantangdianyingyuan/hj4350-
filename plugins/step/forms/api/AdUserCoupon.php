<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\forms\common\coupon\UserCouponData;
use app\models\Coupon;
use app\models\User;
use app\plugins\step\models\StepAdCoupon;

class AdUserCoupon extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;

    public function __construct(Coupon $coupon, User $user)
    {
        $this->coupon = $coupon;
        $this->user = $user;
    }

    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub');
        } else {
            return false;
        }
        $userCouponCenter = new StepAdCoupon();
        $userCouponCenter->mall_id = $this->coupon->mall_id;
        $userCouponCenter->user_id = $this->user->id;
        $userCouponCenter->user_coupon_id = $this->userCoupon->id;
        $userCouponCenter->save();
        return $userCouponCenter->save();
    }

    public function check($coupon)
    {
        return parent::check($coupon);
    }
}