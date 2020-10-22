<?php

namespace app\plugins\bdapp\models;

use app\helpers\CurlHelper;
use app\plugins\bdapp\forms\RsaSign;
use GuzzleHttp\Client;

class PhoneForm extends \app\forms\api\PhoneForm
{

    public function getPhone()
    {
        $config = BdappConfig::findOne([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$config || !$config->app_key || !$config->app_secret) {
            throw new \Exception('百度小程序信息尚未配置。');
        }
        $postData = \Yii::$app->request->post();

        $client = new Client();
        $response = $client->request('post', 'https://spapi.baidu.com/oauth/jscode2sessionkey', [
            'verify' => false,
            'form_params' => [
                'code' => $postData['code'],
                'client_id' => $config->app_key,
                'sk' => $config->app_secret
            ],
        ]);
        $resultJson = $response->getBody()->getContents();
        $tokenData = json_decode($resultJson, true);

        $data = RsaSign::decrypt($postData['data'],$postData['iv'],$config->app_key,$tokenData['session_key']);
        return $data;
    }
}
