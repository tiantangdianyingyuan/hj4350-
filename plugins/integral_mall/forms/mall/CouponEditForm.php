<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;

use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCoupons;

class CouponEditForm extends Model
{
    public $id;
    public $coupon_id;
    public $exchange_num;
    public $integral_num;
    public $send_count;
    public $price;

    public function rules()
    {
        return [
            [['exchange_num', 'integral_num', 'send_count', 'price', 'coupon_id'], 'required'],
            [['exchange_num', 'integral_num', 'send_count', 'id', 'coupon_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'integral_num' => '所需积分',
            'send_count' => '发放总数',
            'exchange_num' => '限购数量',
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $t = \Yii::$app->db->beginTransaction();
        try {
            $model = IntegralMallCoupons::findOne($this->id);
            if (!$model) {
                $model = new IntegralMallCoupons();
                $coupon = Coupon::findOne($this->coupon_id);
                if (!$coupon) {
                    throw new \Exception('coupon_id错误,优惠券不存在');
                }

                if ($coupon->total_count != -1 && $coupon->total_count < $this->send_count) {
                    throw new \Exception('优惠券总数不足,当前总数为' . $coupon->total_count);
                }

                // -1为无限制数量
                if ($coupon->total_count == -1) {
                    $coupon->total_count = -1;
                } else {
                    $coupon->total_count = $coupon->total_count - $this->send_count;
                }
                $res = $coupon->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($coupon));
                }

                $model->coupon_id = $coupon->id;
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->exchange_num = $this->exchange_num;
            $model->integral_num = $this->integral_num;
            $model->send_count = $this->send_count;
            $model->price = $this->price;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            };

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
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
