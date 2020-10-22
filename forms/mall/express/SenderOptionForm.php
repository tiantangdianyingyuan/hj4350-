<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\express;

use app\core\response\ApiCode;
use app\models\Option;
use app\forms\common\CommonOption;
use app\validators\PhoneNumberValidator;
use app\models\Model;

class SenderOptionForm extends Model
{
    const DEFAULT = [
        'company' => '',
        'name' => '',
        'tel' => '',
        'mobile' => '',
        'zip_code' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'address' => '',
    ];

    public $company;
    public $name;
    public $tel;
    public $mobile;
    public $zip_code;
    public $province;
    public $city;
    public $district;
    public $address;

    public function rules()
    {
        return [
            [['name', 'province', 'city', 'district', 'address'], 'required'],
            [['company', 'name', 'tel', 'mobile', 'zip_code', 'province', 'city', 'district', 'address'], 'string', 'max' => 255],
            [['mobile', 'zip_code'], 'default', 'value' => ''],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'company' => '发件人公司',
            'name' => '发件人名称',
            'tel' => '发件人电话',
            'mobile' => '发件人手机',
            'zip_code' => '发件人邮政编码',
            'province' => '发件人省',
            'city' => '发件人市',
            'district' => '发件人区',
            'address' => '发件人详细地址',
        ];
    }

    public function getList()
    {
        return CommonOption::get(
            Option::NAME_DELIVERY_DEFAULT_SENDER,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            senderOptionForm::DEFAULT,
            \Yii::$app->user->identity->mch_id
        );
    }
    
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        if (!$this->tel && !$this->mobile) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '联系方式不能为空'
            ];
        }
        $data = [
            'company' => $this->company,
            'name' => $this->name,
            'tel' => $this->tel,
            'mobile' => $this->mobile,
            'zip_code' => $this->zip_code,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
        ];
        CommonOption::set(
            Option::NAME_DELIVERY_DEFAULT_SENDER,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->user->identity->mch_id
        );
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}
