<?php

namespace app\plugins\aliapp\models;

use Alipay\AopClient;
use app\plugins\aliapp\Plugin;

class PhoneForm extends \app\forms\api\PhoneForm
{

    public function getPhone()
    {
        $plugin = new Plugin();
        $postData = \Yii::$app->request->post('data');
        $config = $plugin->getAliConfig();
        if (empty($config->app_aes_secret)) {
            throw new \Exception('尚未配置AES密钥，请先配置');
        }
        $data = AopClient::decrypt($postData,$config->app_aes_secret);
        $data = json_decode($data,true);
        if (!isset($data['code']) || !isset($data['mobile']) || $data['code'] != '10000') {
            throw new \Exception('获取手机号失败');
        }
        $data['phoneNumber'] = $data['mobile'];
        return $data;
    }
}
