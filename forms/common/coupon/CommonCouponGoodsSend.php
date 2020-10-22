<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/4/15
 * Time: 16:17
 */

namespace app\forms\common\coupon;

use app\models\Coupon;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 * @property User $user
 */
class CommonCouponGoodsSend extends Model
{
    public $mall;
    public $user;
    public $order_id;

    public function send()
    {
        $couponList = [];

        $goodsList = OrderDetail::find()
            ->with('coupon')
            ->where([
                'is_delete' => 0,
                'order_id' => $this->order_id
            ])->all();

        if (!$goodsList) {
            throw new \Exception('商品不存在，无效的order_id');
        }
        /** @var OrderDetail $item */
        foreach ($goodsList as $item) {
            /* @var Coupon[] $goodsCouponList */
            $goodsCouponList = $item->goodsCoupon;
            if (empty($goodsCouponList)) {
                continue;
            }

            $coupons = $item->goodsCoupon;
            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = $this->mall;
            $commonCoupon->user = $this->user;

            foreach ($coupons as $goodsCoupon) {
                $coupon = $goodsCoupon->goodsCoupons;
                if ($coupon->is_delete !== 0) {
                    continue;
                }
                $userCouponGoods = new UserCouponGoods($coupon, $this->user, $item);
                for ($i = 0; $i < bcmul($item->num, $goodsCoupon->num); $i++) {
                    if ($commonCoupon->receive($coupon, $userCouponGoods, '商品赠送优惠券')) {
                        $newCoupon = ArrayHelper::toArray($coupon);
                        if ($newCoupon['expire_type'] == 1) {
                            $newCoupon['desc'] = "本券有效期为发放后{$newCoupon['expire_day']}天内";
                        } else {
                            $newCoupon['desc'] = "本券有效期" . $newCoupon['begin_time'] . "至" . $newCoupon['end_time'];
                        }
                        $newCoupon['content'] = '限';
                        $newCoupon['page_url'] = '/pages/goods/list?coupon_id=' . $coupon->id;
                        if ($coupon->appoint_type == 1) {
                            $catList = [];
                            foreach ($coupon->cat as $cat) {
                                $catList[] = $cat->name;
                            }
                            $newCoupon['content'] .= implode('、', $catList) . '使用';
                        } elseif ($coupon->appoint_type == 2) {
                            $goodsWarehouseList = [];
                            foreach ($coupon->goodsWarehouse as $goodsWarehouse) {
                                $goodsWarehouseList[] = $goodsWarehouse->name;
                                $newCoupon['content'] .= $goodsWarehouse->name . '、';
                            }
                            $newCoupon['content'] .= implode('、', $goodsWarehouseList) . '使用';
                        } elseif ($coupon->appoint_type == 4) {
                            $newCoupon['content'] = '仅限当面付使用';
                            $newCoupon['page_url'] = '/plugins/scan_code/index/index';
                        } else {
                            $newCoupon['content'] = '全场通用';
                        }
                        /** 发放优惠券需要 */
                        $newCoupon['share_type'] = 4;

                        $couponList[] = $newCoupon;
                    }
                }
            }
        }
        return $couponList;
    }
}
