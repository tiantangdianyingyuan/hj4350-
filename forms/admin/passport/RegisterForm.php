<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\passport;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\AdminRegister;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\validators\PhoneNumberValidator;
use app\validators\ValidateCodeValidator;
use yii\helpers\Html;

class RegisterForm extends Model
{
    public $username;
    public $pass;
    public $checkPass;
    public $mobile;
    public $remark;
    public $name;
    public $captcha;
    public $validate_code_id;

    public $wechat_id;
    public $id_card_front_pic;
    public $id_card_back_pic;
    public $business_pic;

    public function rules()
    {
        return [
            [['username', 'pass', 'checkPass', 'mobile', 'remark', 'name', 'captcha', 'validate_code_id'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
            [['captcha'], ValidateCodeValidator::class,
                'mobileAttribute' => 'mobile',
                'validateCodeIdAttribute' => 'validate_code_id',
            ],
            [['wechat_id', 'id_card_front_pic', 'id_card_back_pic', 'business_pic',], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'pass' => '密码',
            'checkPass' => '密码',
            'name' => '姓名/企业名',
            'mobile' => '手机号',
            'remark' => '申请原因',
            'captcha' => '验证码',
            'wechat_id' => '微信号',
            'id_card_front_pic' => '身份证正面',
            'id_card_back_pic' => '身份证反面',
            'business_pic' => '营业执照',
        ];
    }

    public function register()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->pass !== $this->checkPass) {
                throw new \Exception('两次密码输入不一致');
            }

            $adminRegister = AdminRegister::find()->where(['username' => $this->username, 'is_delete' => 0])
                ->andWhere([
                    'or',
                    ['status' => AdminRegister::AUDIT_STATUS_ING],
                    ['status' => AdminRegister::AUDIT_STATUS_TRUE],
                ])->one();

            $userExist = User::find()->alias('u')->where(['u.username' => $this->username, 'u.is_delete' => 0])
                ->joinWith(['identity' => function ($query) {
                    $query->alias('i')->andWhere([
                        'or',
                        ['i.is_super_admin' => 1],
                        ['i.is_admin' => 1]
                    ]);
                }])->one();

            if ($adminRegister && !($adminRegister->status == AdminRegister::AUDIT_STATUS_TRUE && !$userExist)) {
                throw new \Exception('您已提交过申请，请勿重复提交');
            }

            if ($userExist) {
                throw new \Exception('用户已存在');
            }

            $setting = CommonOption::get(Option::NAME_IND_SETTING);
            if ($setting['is_required'] == 1) {
                if (!$this->id_card_front_pic) {
                    throw new \Exception('请上传身份证正面照');
                }
                if (!$this->id_card_back_pic) {
                    throw new \Exception('请上传身份证反面照');
                }
                if (!$this->business_pic) {
                    throw new \Exception('请上传营业执照');
                }
            }

            $adminRegister = new AdminRegister();
            $adminRegister->username = $this->username;
            $adminRegister->password = $this->checkPass;
            $adminRegister->mobile = $this->mobile;
            $adminRegister->name = $this->name;
            $adminRegister->remark = $this->remark;
            $adminRegister->wechat_id = $this->wechat_id;
            $adminRegister->id_card_front_pic = $this->id_card_front_pic;
            $adminRegister->id_card_back_pic = $this->id_card_back_pic;
            $adminRegister->business_pic = $this->business_pic;
            $res = $adminRegister->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($adminRegister));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '注册信息已提交，请等待管理员审核'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
