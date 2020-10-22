<?php

namespace app\controllers\api;

use app\forms\api\LoginForm;
use app\forms\api\ManPhoneForm;

class PhoneController extends ApiController
{
    public function actionBinding()
    {
        $form = $this->getBindingForm();
        return $form->binding();
    }

    /**
     * @return ManPhoneForm
     * @throws \Exception
     */
    private function getBindingForm()
    {
        if (\Yii::$app->request->post('mobile')) {
            $form = new ManPhoneForm();
            $form->attributes = \Yii::$app->request->post();
            return $form;
        }
        $appPlatform = \Yii::$app->appPlatform;
        $Class = null;
        if ($appPlatform === APP_PLATFORM_WXAPP) {
            $Class = 'app\\plugins\\wxapp\\models\\PhoneForm';
        }
        if ($appPlatform === APP_PLATFORM_ALIAPP) {
            $Class = 'app\\plugins\\aliapp\\models\\PhoneForm';
        }
        if ($appPlatform === APP_PLATFORM_BDAPP) {
            $Class = 'app\\plugins\\bdapp\\models\\PhoneForm';
        }
        if ($appPlatform === APP_PLATFORM_TTAPP) {
            $Class = 'app\\plugins\\ttapp\\models\\PhoneForm';
        }
        if (!$Class || !class_exists($Class)) {
            throw new \Exception('未安装相关平台的插件或未知的客户端平台，平台标识`' . ($appPlatform ? $appPlatform : 'null') . '`');
        }
        return new $Class();
    }
}
