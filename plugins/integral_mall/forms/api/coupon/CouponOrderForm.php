<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api\coupon;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;

class CouponOrderForm extends Model
{
    public $id;
    public $page;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = IntegralMallCouponsOrders::find()->where([
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])
            ->with('integralCoupon.coupon')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $detail = IntegralMallCouponsOrders::find()->where([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])
            ->with('integralCoupon.coupon')->asArray()->one();

        if (!$detail) {
            throw new \Exception('优惠券订单详情不存在');
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail,
            ]
        ];
    }
}
