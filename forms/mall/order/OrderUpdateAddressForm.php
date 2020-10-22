<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\Model;
use app\validators\PhoneNumberValidator;

class OrderUpdateAddressForm extends Model
{
    public $order_id;
    public $name;
    public $mobile;
    public $address;
    public $province;
    public $city;
    public $district;
    public $mch_id;

    public function rules()
    {
        return [
            [['order_id', 'name', 'mobile', 'address'], 'required'],
            [['order_id', 'mch_id'], 'integer'],
            [['name', 'address', 'province', 'city', 'district', 'mobile'], 'string'],
            [['mch_id'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'address' => '详细地址',
            'mobile' => '手机号',
            'name' => '收件人',
        ];
    }

    //更新收货地址
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::findOne(['id' => $this->order_id, 'mall_id' => \Yii::$app->mall->id, 'mch_id' => $this->mch_id]);
        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }

        if ($order->is_send == 0 && $order->send_type == 1) {
            $order->send_type = 0;
        }

        $order->name = $this->name;
        $order->mobile = $this->mobile;
        $order->address = $this->province . ' ' . $this->city . ' ' . $this->district . ' ' . $this->address;
        if ($order->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } else {
            return $this->getErrorResponse($order);
        }
    }
}
