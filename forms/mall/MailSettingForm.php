<?php
/**
 * @link:http://www.zjhejiang.com/
 * @copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 *
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2018/12/10
 * Time: 9:56
 */

namespace app\forms\mall;


use app\core\mail\SendMail;
use app\core\response\ApiCode;
use app\models\MailSetting;
use app\models\Model;

/**
 * @property MailSetting $model
 */
class MailSettingForm extends Model
{
    public $status;

    public $send_mail;
    public $send_pwd;
    public $send_name;
    public $receive_mail;
    public $test;
    public $show_type;

    public $model;

    public function rules()
    {
        return [
            [['status', 'test'], 'integer'],
            ['status', 'default', 'value' => 0],
            [['send_mail', 'send_pwd', 'send_name'], 'string'],
            [['send_mail', 'send_pwd', 'send_name', 'show_type'], 'trim'],
            [['receive_mail'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        if ($this->status == 1) {
            if (!($this->send_mail && $this->send_pwd && $this->send_name && $this->receive_mail)) {
                return [
                    'code' => 1,
                    'msg' => '请填写信息'
                ];
            }
        }
        $this->receive_mail = $this->receive_mail ? implode(',', $this->receive_mail) : '';
        $this->model->attributes = $this->attributes;
        $this->model->show_type = \yii\helpers\BaseJson::encode($this->show_type);
        if ($this->test) {
            return $this->test();
        }
        if ($this->model->isNewRecord) {
            $this->model->is_delete = 0;
            $this->model->mall_id = \Yii::$app->mall->id;
            $this->model->mch_id = \Yii::$app->user->identity->mch_id;
        }
        if ($this->model->save()) {
            return [
                'code' => 0,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($this->model);
        }
    }

    public function test()
    {
        try {
            $mailer = new SendMail();
            $mailer->mall = \Yii::$app->mall;
            $mailer->mailSetting = $this->model;
            $mailer->test();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发送成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '发送失败，请检查发件人邮箱、授权码及收件人邮箱是否正确',
                'data' => $exception
            ];
        }
    }
}
