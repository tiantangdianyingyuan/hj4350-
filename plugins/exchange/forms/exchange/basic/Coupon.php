<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;

use app\forms\common\coupon\CommonCoupon;
use app\models\Mall;

class Coupon extends BaseAbstract implements Base
{
    public function exchange(&$message)
    {
        try {
            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = Mall::findOne($this->user->mall_id);
            $commonCoupon->user = $this->user;
            $coupon = \app\models\Coupon::findOne([
                'id' => $this->config['coupon_id'],
                'is_delete' => 0,
            ]);
            if (!$coupon) {
                throw new \Exception('优惠券不存在');
            }
            $send_num = $this->config['coupon_num'];
            $class = new CouponRelation($coupon, $this->codeModel);
            $desc = sprintf('兑换码%s获得', $this->codeModel->code);
            $result = $commonCoupon->receive($coupon, $class, $desc, $send_num) === true;
            if (!$result) {
                throw new \Exception('优惠券领取失败');
            }
            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
