<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\home_page;


use app\forms\common\coupon\CommonCouponList;
use app\models\Model;

class HomeCouponForm extends Model
{
    public function getCouponList()
    {
        $common = new CommonCouponList();
        $common->user = \Yii::$app->user->identity;
        $list = $common->getList();

        return $common->getIndexData($list);
    }
}
