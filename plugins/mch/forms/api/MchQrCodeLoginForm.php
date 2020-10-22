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
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\UserAuthLogin;

class MchQrCodeLoginForm extends Model
{
    public $token;

    public function rules()
    {
        return [
            [['token'], 'required'],
            [['token'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'token' => 'Token',
        ];
    }

    public function qrCodeLogin()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $authToken = UserAuthLogin::findOne([
                'token' => $this->token,
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$authToken) {
                throw new \Exception('二维码不存在');
            }

            if ($authToken->is_pass != 0) {
                throw new \Exception('二维码已失效');
            }

            $mch = Mch::findOne([
                'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            if (!$mch) {
                throw new \Exception('账号无关联商户');
            }
            if ($mch->review_status != 1) {
                throw new \Exception('店铺未通过审核');
            }

            $authToken->is_pass = 1;
            $authToken->user_id = \Yii::$app->user->id;
            $res = $authToken->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($authToken));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '登录成功',
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
