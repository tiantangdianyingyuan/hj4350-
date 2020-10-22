<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/5/7
 * Time: 17:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\mall_member;


use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\coupon\UserCouponMember;
use app\models\Model;
use yii\helpers\ArrayHelper;

class CouponForm extends Model
{
    public $coupon_id;

    public function rules()
    {
        return [
            ['coupon_id', 'required'],
            ['coupon_id', 'integer'],
        ];
    }

    public function receive()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $common = new CommonCoupon($this->attributes, false);
            $common->user = \Yii::$app->user->identity;
            $coupon = $common->getDetail();
            if ($coupon->is_delete == 1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '优惠券不存在'
                ];
            }
            $count = $common->checkAllReceive($coupon->id);
            if ($count > $coupon->can_receive_count && $coupon->can_receive_count != -1) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '已领取优惠券'
                ];
            } else {
                $class = new UserCouponMember($coupon, $common->user);
                if ($common->receive($coupon, $class, '会员中心领取')) {
                    if ($coupon->can_receive_count == -1) {
                        $rest = -1;
                    } elseif ($coupon->can_receive_count <= $count + 1) {
                        $rest = 0;
                    } else {
                        $rest = $coupon->can_receive_count - $count - 1;
                    }
                    $coupon = ArrayHelper::toArray($coupon);
                    $coupon['rest'] = $rest;
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '领取成功',
                        'data' => $coupon
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '优惠券已领完'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
