<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/11
 * Time: 9:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\coupon;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\coupon\CommonCoupon;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserCouponCenter;
use app\models\UserCouponMember;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserCouponDetailForm extends Model
{
    public $user;
    public $mall;

    public $user_coupon_id;

    public function rules()
    {
        return [
            [['user_coupon_id'], 'required']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $couponCommon = new CommonCoupon();
            $couponCommon->mall = $this->mall;
            $couponCommon->user = $this->user;
            /* @var UserCoupon $userCoupon */
            $userCoupon = $couponCommon->getUserCoupon($this->user_coupon_id);
            if (!$userCoupon) {
                throw new \Exception('用户优惠券不存在或已删除');
            }

            $isExpired = 0;
            if (strtotime($userCoupon->end_time) < time()) {
                $isExpired = 1;
            }
            $status = 1;
            if ($userCoupon->is_use == 1) {
                $status = 2;
            } elseif ($isExpired == 1) {
                $status = 3;
            }
            $data = [
                'sub_price' => $userCoupon->sub_price,
                'discount' => price_format($userCoupon->discount, 'string' , 1),
                'min_price' => $userCoupon->coupon_min_price,
                'type' => $userCoupon->type,
                'start_time' => new_date($userCoupon->start_time),
                'begin_time' => new_date($userCoupon->start_time),
                'end_time' => new_date($userCoupon->end_time),
                'is_use' => $userCoupon->is_use,
                'is_expired' => $isExpired,
                'receive_type' => $userCoupon->receive_type,
                'name' => $userCoupon->coupon->name,
                'pic_url' => $userCoupon->coupon->pic_url,
                'desc' => $userCoupon->coupon->desc,
                'rule' => $userCoupon->coupon->rule,
                'appoint_type' => $userCoupon->coupon->appoint_type,
                'goods' => $userCoupon->coupon->getGoods()->select('name')->all(),
                'cat' => $userCoupon->coupon->getCat()->select('name')->all(),
                'coupon_id' => $userCoupon->coupon_id,
                'discount_limit' => $userCoupon->discount_limit,
                'page_url' => $userCoupon->coupon->appoint_type == 4 ? '/plugins/scan_code/index/index' : '/pages/goods/list?coupon_id=' . $userCoupon->coupon_id,
                'id' => $userCoupon->coupon->id,
                'status' => $status,
                'expire_type' => '2',
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $data
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
