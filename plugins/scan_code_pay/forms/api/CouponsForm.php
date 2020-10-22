<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;


use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Model;
use app\models\UserCoupon;
use yii\helpers\ArrayHelper;

class CouponsForm extends Model
{
    public $price;

    public function rules()
    {
        return [
            [['price'], 'required'],
            [['price'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'price' => '金额'
        ];
    }

    public function getCoupons()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $allList = $this->getList();

            $newCoupons = [];
            foreach ($allList as $coupon) {
                $arr = ArrayHelper::toArray($coupon);
                $arr['coupon_data'] = \Yii::$app->serializer->decode($arr['coupon_data']);
                $newCoupons[] = $arr;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'coupon_list' => $newCoupons
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // TODO 优惠金额之后价格为0 的情况下，再选择优惠券
        if ($this->price <= 0) {
            throw new \Exception('金额必须大于0');
        }

        $couponIds = Coupon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'appoint_type' => 4,
        ])->select('id');

        /** @var UserCoupon[] $allList */
        $query = UserCoupon::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id,],
            ['user_id' => \Yii::$app->user->id],
            ['is_use' => 0],
            ['is_delete' => 0],
            ['<=', 'start_time', mysql_timestamp()],
            ['>=', 'end_time', mysql_timestamp()],
            ['<=', 'coupon_min_price', $this->price],
        ])->andWhere(['coupon_id' => $couponIds]);

        $allList = $query->all();

        return $allList;
    }
}