<?php
namespace app\forms\api\user;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\core\sms\Sms;
use app\validators\PhoneNumberValidator;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class SmsForm extends Model
{
    public $mobile;
    public $code;

    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
            [['code'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'code' => '验证码',
        ];
    }

    public function code()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $sms = new Sms();
            $res = CommonOption::get(
                Option::NAME_SMS,
                \Yii::$app->mall->id,
                Option::GROUP_ADMIN
            );
            if(!$res || $res['status'] == 0) {
                throw new \Exception('验证码功能未开启');
            };
            $sms->sendCaptcha($this->mobile);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '验证码获取成功'
            ];
        } catch (\Exception $exception) {
            if($exception instanceof NoGatewayAvailableException) {
                //$exception = $exception->results['aliyun']['exception'];
                $msg = '验证码配置错误';
            } else {
                $msg = $exception->getMessage();
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $msg
            ];
        }
    }

    public function empower()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $user = User::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => \Yii::$app->user->id,
                'is_delete' => 0
            ]);

        $message = Sms::checkValidateCode($this->mobile, $this->code);
        if (!$message) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '验证码不正确',
            ];
        }
        $user->mobile = $this->mobile;
        if ($user->save()) {
            Sms::updateCodeStatus($this->mobile, $this->code);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '绑定成功',
                'data' => [
                    'mobile' => $this->mobile,
                ]
            ];
        } else {
            return $this->getErrorResponse($user);
        }
    }
}
