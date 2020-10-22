<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/21
 * Time: 16:43
 */

namespace app\forms\api;

use app\models\CoreValidateCode;
use app\validators\PhoneNumberValidator;

class ManPhoneForm extends PhoneForm
{
    public $mobile;
    public $mobile_code;

    public function rules()
    {
        return [
            [['mobile', 'mobile_code'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'mobile_code' => '短信验证码'
        ];
    }

    protected function getPhone()
    {
        $this->validateCode();
        $data['phoneNumber'] = $this->mobile;
        return $data;
    }

    private function validateCode()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }

        $coreValidateCode = CoreValidateCode::find()->where([
            'target' => $this->mobile,
            'code' => $this->mobile_code,
        ])->one();

        if (!$coreValidateCode) {
            throw new \Exception('验证码不正确');
        }

        if ($coreValidateCode->is_validated == 1) {
            throw new \Exception('验证码已失效');
        }

        $coreValidateCode->is_validated = 1;
        $res = $coreValidateCode->save();
    }
}