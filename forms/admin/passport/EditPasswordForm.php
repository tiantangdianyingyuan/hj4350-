<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\passport;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\validators\PhoneNumberValidator;

class EditPasswordForm extends Model
{
    public $username;
    public $pass;
    public $checkPass;
    public $mobile;
    public $captcha;
    public $user_type;

    public function rules()
    {
        return [
            [['username', 'pass', 'checkPass', 'mobile', 'captcha', 'user_type'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'pass' => '密码',
            'checkPass' => '密码',
            'mobile' => '手机号',
            'captcha' => '验证码',
            'user_type' => '用户类型',
        ];
    }

    public function editPassword()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // TODO 验证 验证码是否正确

        if ($this->pass !== $this->checkPass) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '两次密码输入不一致'
            ];
        }

        // 只允许管理员修改密码、操作员不允许修改
        if ($this->user_type != 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '账号用户类型异常'
            ];
        }

        $user = User::find()->joinWith(['identity' => function ($query) {
            $query->andWhere([
                'or',
                ['is_super_admin' => 1],
                ['is_admin' => 1]
            ]);
        }])->where(['username' => $this->username])->one();

        if (!$user) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '用户不存在',
            ];
        }

        $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->checkPass);
        $res = $user->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }
}
