<?php


namespace app\plugins\step\forms\common;

use Alipay\AopClient;

class CommonSport
{
    public function getSportClass($data)
    {
        $appPlatform = \Yii::$app->appPlatform;
        //微信
        if ($appPlatform === APP_PLATFORM_WXAPP) {
            $plugin = new \app\plugins\wxapp\Plugin();
            $data = $plugin->getWechat()->decryptData(
                $data{'encrypted_data'},
                $data{'iv'},
                $data{'code'}
            );
            return end($data['stepInfoList'])['step'];
        }
        //支付宝
        if ($appPlatform === APP_PLATFORM_ALIAPP) {
            $plugin = new \app\plugins\aliapp\Plugin();
            $config = $plugin->getAliConfig();
            if (empty($config->app_aes_secret)) {
                throw new \Exception('尚未配置AES密钥，请先配置');
            }
            $data = AopClient::decrypt($data{'encrypted_data'}, $config->app_aes_secret);
            $data = \yii\helpers\BaseJson::decode($data);
            if ($data['code'] === '10000') {
                return $data['count'];
            }
            throw new \Exception($data['subMsg']);
        }
        throw new \Exception($appPlatform . '平台不支持获取步数');
    }
}