<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\passport;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\UserAuthLogin;

class MchQrCodePassportForm extends Model
{
    public $mall_id;
    public $token;

    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id'], 'string'],
            [['token'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getLoginQrCode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mallId = base64_decode($this->mall_id);

        try {
            $mall = Mall::findOne($mallId);
            if (!$mall) {
                throw new \Exception('商城不存在');
            }
            \Yii::$app->setMall($mall);
            $token = \Yii::$app->security->generateRandomString();
            $userAuthLogin = new UserAuthLogin();
            $userAuthLogin->mall_id = $mallId;
            $userAuthLogin->token = $token;
            $res = $userAuthLogin->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($userAuthLogin));
            }

            $commonQrCode = new CommonQrCode();
            $res = $commonQrCode->getQrCode(['token' => $token], 100, 'plugins/mch/mch/web-login/web-login');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'data' => $res,
                    'token' => $token,
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

    public function checkMchLogin()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $authToken = UserAuthLogin::findOne(['token' => $this->token, 'mall_id' => base64_decode($this->mall_id)]);
            if (!$authToken) {
                throw new \Exception('token不存在');
            }

            if (!$authToken->is_pass) {
                throw new \Exception('待扫码确认');
            }

            $mch = Mch::findOne([
                'user_id' => $authToken->user_id,
                'mall_id' => $authToken->mall_id,
                'is_delete' => 0
            ]);
            if (!$mch) {
                throw new \Exception('账号无关联商户');
            }
            if ($mch->review_status != 1) {
                throw new \Exception('店铺未通过审核');
            }

            $user = User::findOne(['mch_id' => $mch->id, 'is_delete' => 0]);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $res = \Yii::$app->user->login($user);
            $id = \Yii::$app->user->getId();

            $route = 'mall/index/index';
            setcookie('__login_route', '/admin/passport/mch-login');
            setcookie('__mall_id', $user->mall_id);
            setcookie('__login_role', 'mch');
            \Yii::$app->setSessionMallId($user->mall_id);
            \Yii::$app->session->set('__mch_id', $mch->id);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '正在登录中..',
                'data' => [
                    'url' => $route
                ]
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
