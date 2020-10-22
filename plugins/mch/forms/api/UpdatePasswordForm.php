<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;

class UpdatePasswordForm extends Model
{
    public $mch_id;
    public $password;

    public function rules()
    {
        return [
            [['mch_id', 'password'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => '密码',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var User $user */
            $user = User::find()->where([
                'mch_id' => $this->mch_id,
                'is_delete' => 0,
            ])->one();

            if (!$user) {
                throw new \Exception('账号不存在');
            }

            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $this->password) > 0) {
                throw new \Exception('密码不能包含中文字符');
            }

            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '密码修改成功',
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
