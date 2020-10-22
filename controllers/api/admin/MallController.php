<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/31
 * Time: 14:02
 */

namespace app\controllers\api\admin;

use app\forms\common\CommonOption;
use app\forms\mall\index\MallForm;
use app\forms\mall\index\SettingForm;
use app\forms\mall\MailSettingForm;
use app\forms\mall\sms\SmsEditForm;
use app\forms\mall\sms\SmsForm;
use app\models\MailSetting;
use app\models\Option;

class MallController extends AdminController
{
    public function actionSetting()
    {
        $mail = MailSetting::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => \Yii::$app->user->identity->mch_id
        ]);

        if (\Yii::$app->request->isGet) {
            $form = new MallForm();
            $res = $form->getDetail();

            $sms = CommonOption::get(
                Option::NAME_SMS,
                \Yii::$app->mall->id,
                Option::GROUP_ADMIN,
                null,
                \Yii::$app->user->identity->mch_id
            );

            if (empty($mail) || !($mail->send_mail && $mail->send_pwd && $mail->send_name && $mail->receive_mail)) {
                $mail = null;
            }

            $res['data']['sms'] = $sms;
            $res['data']['mail'] = $mail;

            return $this->asJson($res);
        } else {
            $form = new SettingForm();
            $data = \Yii::$app->request->post();
            $form->name = $data['name'];
            $setting = json_decode($data['setting'], true);
            foreach ($setting as &$v) {
                if (is_int($v) || is_float($v)) {
                    $v = (string)$v;
                }
            }
            unset($v);
            $form->attributes = $setting;
            $form->over_time = (string)$setting['over_time'];
            $form->delivery_time = (string)$setting['delivery_time'];
            $form->after_sale_time = (string)$setting['after_sale_time'];

            if (isset($data['sms'])) {
                $sms = new SmsEditForm();
                $smsOption = CommonOption::get(Option::NAME_SMS, \Yii::$app->mall->id, Option::GROUP_ADMIN);
                $smsOption['status'] = $data['sms'];
                $sms->data = $smsOption;
                $sms->save();
            }

            if (isset($data['mail'])) {
                if (!$mail) {
                    $mail = new MailSetting();
                    $mail->mall_id = \Yii::$app->mall->id;
                    $mail->mch_id = \Yii::$app->user->identity->mch_id;
                }
                if ($mail->receive_mail) {
                    $mail->receive_mail = explode(',', $mail->receive_mail);
                } else {
                    $mail->receive_mail = [];
                }
                $mailform = new MailSettingForm();
                $mailform->model = $mail;
                $mailform->attributes = $mail->attributes;
                $mailform->status = $data['mail'];
                $mailform->save();
            }

            return $this->asJson($form->save());
        }
    }
}