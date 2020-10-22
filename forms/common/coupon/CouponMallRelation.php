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
class CouponMallRelation extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;

    public function __construct(Coupon $coupon)
    {
        $this->coupon = $coupon;
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
        $CouponMallRelation = new \app\models\CouponMallRelation();
        $CouponMallRelation->mall_id = $this->coupon->mall_id;
        $CouponMallRelation->user_coupon_id = $this->userCoupon->id;
        $CouponMallRelation->is_delete = 0;
        return $CouponMallRelation->save();
    }
}
