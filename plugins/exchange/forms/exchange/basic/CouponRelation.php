<?php

namespace app\plugins\exchange\forms\exchange\basic;

use app\forms\common\coupon\UserCouponData;
use app\models\Coupon;
use app\plugins\exchange\models\ExchangeCouponRelation;

class CouponRelation extends UserCouponData
{
    public $coupon;
    public $codeModel;

    public function __construct(Coupon $coupon, $codeModel)
    {
        $this->coupon = $coupon;
        $this->codeModel = $codeModel;
    }

    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub', $this->coupon->id);
        } else {
            return false;
        }
        $coupon = new ExchangeCouponRelation();
        $coupon->mall_id = $this->coupon->mall_id;
        $coupon->code_id = $this->codeModel->id;
        $coupon->user_coupon_id = $this->userCoupon->id;
        return $coupon->save();
    }
}
