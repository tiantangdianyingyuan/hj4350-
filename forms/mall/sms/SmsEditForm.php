<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\sms;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOption;
use app\forms\common\CommonSms;
use app\models\Model;
use app\models\Option;
use Overtrue\EasySms\Message;

class SmsEditForm extends Model
{
    public $data;

    public $status;
    public $platform;
    public $access_key_id;
    public $access_key_secret;
    public $order;
    public $captcha;
    public $order_refund;
    public $mobile_list;

    public function rules()
    {
        return [
            [['status', 'platform', 'access_key_id', 'access_key_secret'], 'required'],
            [['platform', 'access_key_id', 'access_key_secret'], 'string'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => 0],
            [['platform', 'access_key_id', 'access_key_secret'], 'trim'],
            [['order', 'order_refund', 'captcha', 'mobile_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '短信开关',
            'platform' => '模板签名',
        ];
    }

    public function save()
    {
        try {
            $res = CommonOption::set(
                Option::NAME_SMS,
                $this->data,
                \Yii::$app->mall->id,
                Option::GROUP_ADMIN,
                \Yii::$app->user->identity->mch_id
            );

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function testSms($type)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $smsConfig = CommonAppConfig::getSmsConfig();
            if ($smsConfig['status'] != 1) {
                throw new \Exception('短信功能未开启');
            }
            if (!is_array($smsConfig['mobile_list']) || count($smsConfig['mobile_list']) <= 0) {
                throw new \Exception('接收短信手机号不正确');
            }
            $setting = CommonSms::getCommon()->getSetting();
            if (!(isset($smsConfig[$type])
                && isset($smsConfig[$type]['template_id'])
                && $smsConfig[$type]['template_id'])) {
                throw new \Exception($setting[$type]['title'] . '模板ID未设置');
            }
            $data = [];
            foreach ($setting[$type]['variable'] as $value) {
                $data[$smsConfig[$type][$value['key']]] = '89757';
            }
            $message = new Message([
                'template' => $smsConfig[$type]['template_id'],
                'data' => $data
            ]);
            $sms = \Yii::$app->sms->module('mall');
            foreach ($smsConfig['mobile_list'] as $mobile) {
                $sms->send($mobile, $message);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '短信发送成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage() . ';请先保存好短信发送配置',
            ];
        }
    }
}
