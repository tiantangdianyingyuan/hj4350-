<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/15
 * Time: 15:20
 */

namespace app\forms\common\coupon;

use app\models\Coupon;
use app\models\OrderDetail;
use app\models\User;

/**
 * @property User $user
 */
class UserCouponGoods extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;
    public $order;

    public function __construct(Coupon $coupon, User $user, OrderDetail $order)
    {
        $this->coupon = $coupon;
        $this->user = $user;
        $this->order = $order;
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
        $userCouponGoods = new \app\models\UserCouponGoods();
        $userCouponGoods->mall_id = $this->coupon->mall_id;
        $userCouponGoods->user_id = $this->user->id;
        $userCouponGoods->goods_id = $this->order->goods_id;
        $userCouponGoods->user_coupon_id = $this->userCoupon->id;
        return $userCouponGoods->save();
    }
}
