<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/1
 * Time: 16:33
 */

namespace app\forms\admin\passport;


use app\core\response\ApiCode;
use app\forms\admin\SmsCaptchaForm;
use app\models\Model;
use app\models\User;

class SendRestPasswordCaptchaForm extends Model
{
    public $mobile;

    public function rules()
    {
        return [
            [['mobile'], 'trim'],
            [['mobile'], 'required'],
        ];
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $users = User::find()
            ->select('id,mobile,username,nickname')->where([
                'mobile' => $this->mobile,
                'mall_id' => 0,
                'is_delete' => 0,
            ])->all();
        if (!$users) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该手机号尚未注册',
            ];
        }
        $captchaForm = new SmsCaptchaForm();
        $captchaForm->mobile = $this->mobile;
        $res = $captchaForm->send();
        if ($res['code'] == 1) {
            return $res;
        }
        $res['data']['user_list'] = $users;
        return $res;
    }
}
