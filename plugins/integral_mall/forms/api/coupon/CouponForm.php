<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api\coupon;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCoupons;

class CouponForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    public function detail()
    {
        $detail = IntegralMallCoupons::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id
        ])->with('coupon.cat', 'coupon.goods')->asArray()->one();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail
            ]
        ];
    }
}
