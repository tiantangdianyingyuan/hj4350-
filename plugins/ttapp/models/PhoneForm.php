<?php

namespace app\plugins\ttapp\models;

use app\plugins\ttapp\Plugin;

class PhoneForm extends \app\forms\api\PhoneForm
{

    public function getPhone()
    {
        $plugin = new Plugin();
        $postData = \Yii::$app->request->post();
        $data = $plugin->decryptData(
            $postData['encryptedData'],
            $postData['iv'],
            $postData['code']
        );
        return $data;
    }
}
