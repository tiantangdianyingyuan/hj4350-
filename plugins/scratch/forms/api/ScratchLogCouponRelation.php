<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 14:28
 */

namespace app\plugins\scratch\forms\api;

use app\models\Coupon;
use app\plugins\scratch\models\ScratchLog;
use app\forms\common\coupon\UserCouponData;

/**
 * @property User $user
 */
class ScratchLogCouponRelation extends UserCouponData
{
    public $coupon;
    public $scratchLog;
    public $userCoupon;

    public function __construct(Coupon $coupon, ScratchLog $scratchLog)
    {
        $this->coupon = $coupon;
        $this->scratchLog = $scratchLog;
    }

    public function save()
    {
        $ScratchLogCouponRelation = new \app\plugins\scratch\models\ScratchLogCouponRelation();
        $ScratchLogCouponRelation->mall_id = \Yii::$app->mall->id;
        $ScratchLogCouponRelation->user_coupon_id = $this->userCoupon->id;
        $ScratchLogCouponRelation->scratch_log_id = $this->scratchLog->id;
        $ScratchLogCouponRelation->is_delete = 0;
        return $ScratchLogCouponRelation->save();
    }
}
