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
use app\plugins\mch\models\Mch;

class MchPassportForm extends Model
{
    public $username;
    public $password;
    public $pic_captcha;
    public $mall_id;
    public $checked;

    public function rules()
    {
        return [
            [['username', 'password', 'mall_id', 'checked'], 'required'],
            [['pic_captcha'], 'captcha', 'captchaAction' => 'site/pic-captcha'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var User $user */
            $user = User::find()->where([
                'username' => $this->username,
                'is_delete' => 0,
                'mall_id' => base64_decode($this->mall_id)
            ])->andWhere(['!=', 'mch_id', 0])->one();

            if (!$user) {
                throw new \Exception('账号不存在');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $user->password)) {
                throw new \Exception('密码错误');
            }

            $mch = Mch::findOne([
                'id' => $user->mch_id,
                'mall_id' => $user->mall_id,
                'is_delete' => 0
            ]);
            if (!$mch) {
                throw new \Exception('账号无关联商户');
            }
            if ($mch->review_status != 1) {
                throw new \Exception('店铺未通过审核');
            }

            $duration = $this->checked == 'true' ? 86400 : 0;
            $res = \Yii::$app->user->login($user, $duration);
            setcookie('__login_route', '/admin/passport/mch-login');
            setcookie('__mall_id', $user->mall_id);
            setcookie('__login_role', 'mch');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '登录成功',
                'data' => [
                    'url' => 'mall/index/index'
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
}
