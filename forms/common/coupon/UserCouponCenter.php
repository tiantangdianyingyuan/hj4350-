<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 14:28
 */

namespace app\forms\common\coupon;


use app\models\Coupon;
use app\models\User;

/**
 * @property User $user
 */
class UserCouponCenter extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;

    public function __construct(Coupon $coupon, User $user)
    {
        $this->coupon = $coupon;
        $this->user = $user;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub');
        } else {
            return false;
        }
        $userCouponCenter = new \app\models\UserCouponCenter();
        $userCouponCenter->mall_id = $this->coupon->mall_id;
        $userCouponCenter->user_id = $this->user->id;
        $userCouponCenter->user_coupon_id = $this->userCoupon->id;
        return $userCouponCenter->save();
    }
}
