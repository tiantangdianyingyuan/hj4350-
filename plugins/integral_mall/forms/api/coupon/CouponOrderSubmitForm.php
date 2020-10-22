<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api\coupon;

use app\core\currency\IntegralModel;
use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\integral_mall\jobs\CreateCouponOrderJob;
use app\plugins\integral_mall\models\IntegralMallCouponOrderSubmitResult;
use app\plugins\integral_mall\models\IntegralMallCoupons;

class CouponOrderSubmitForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $coupon = IntegralMallCoupons::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('coupon')->one();

            if (!$coupon) {
                throw new \Exception('优惠券不存在');
            }

            $token = \Yii::$app->security->generateRandomString();
            $queueId = \Yii::$app->queue->delay(0)->push(new CreateCouponOrderJob([
                'coupon' => $coupon,
                'mall' => \Yii::$app->mall,
                'user' => User::findOne(\Yii::$app->user->id),
                'token' => $token
            ]));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId,
                    'token' => $token
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
}
