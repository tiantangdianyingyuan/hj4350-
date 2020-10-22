<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\api;

use app\forms\common\coupon\UserCouponData;
use app\models\Coupon;
use app\models\User;
use app\plugins\diy\models\DiyCouponLog;

class UserCoupon extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;
    public $template_id;

    public function __construct(Coupon $coupon, int $template_id, User $user)
    {
        $this->coupon = $coupon;
        $this->template_id = $template_id;
        $this->user = $user;
    }

    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub');
        } else {
            return false;
        }
        $userCouponCenter = new DiyCouponLog();
        $userCouponCenter->mall_id = $this->coupon->mall_id;
        $userCouponCenter->template_id = $this->template_id;
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