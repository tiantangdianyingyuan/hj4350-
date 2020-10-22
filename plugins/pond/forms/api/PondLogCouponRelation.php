<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 14:28
 */

namespace app\plugins\pond\forms\api;

use app\models\Coupon;
use app\plugins\pond\models\PondLog;
use app\forms\common\coupon\UserCouponData;

/**
 * @property User $user
 */
class PondLogCouponRelation extends UserCouponData
{
    public $coupon;
    public $pondLog;
    public $userCoupon;

    public function __construct(Coupon $coupon, PondLog $pondLog)
    {
        $this->coupon = $coupon;
        $this->pondLog = $pondLog;
    }

    public function save()
    {

        $PondLogCouponRelation = new \app\plugins\pond\models\PondLogCouponRelation();
        $PondLogCouponRelation->mall_id = \Yii::$app->mall->id;
        $PondLogCouponRelation->user_coupon_id = $this->userCoupon->id;
        $PondLogCouponRelation->pond_log_id = $this->pondLog->id;
        $PondLogCouponRelation->is_delete = 0;
        return $PondLogCouponRelation->save();
    }
}
