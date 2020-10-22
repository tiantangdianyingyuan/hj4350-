<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\order_send_template;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderSendTemplateAddress;

class AddressEditForm extends Model
{
    public $name;
    public $username;
    public $mobile;
    public $code;
    public $province;
    public $city;
    public $district;
    public $address;

    public function rules()
    {
        return [
            [['name', 'username', 'mobile', 'code', 'province', 'city', 'district', 'address'], 'required'],
            [['name', 'username', 'mobile', 'code'], 'string', 'max' => 60],
            [['address'], 'string', 'max' => 255]
        ];
    }


    public function attributeLabels()
    {
        return [
            'name' => '网点名称',
            'username' => '联系人',
            'mobile' => '联系电话',
            'code' => '网点邮编',
            'province' => '省',
            'city' => '市',
            'district' => '区',
            'address' => '详细地址',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $templateAddress = OrderSendTemplateAddress::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
            ])->one();

            if (!$templateAddress) {
                $templateAddress = new OrderSendTemplateAddress();
                $templateAddress->mall_id = \Yii::$app->mall->id;
                $templateAddress->mch_id = \Yii::$app->user->identity->mch_id;
            }

            $templateAddress->attributes = $this->attributes;
            $templateAddress->address = json_encode([
                'province' => $this->province,
                'city' => $this->city,
                'district' => $this->district,
                'address' => $this->address,
            ], true);

            if (!$templateAddress->save()) {
                throw new \Exception($this->getErrorMsg($templateAddress));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}