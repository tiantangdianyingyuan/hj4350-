<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/7/30
 * Time: 10:51
 */

namespace app\plugins\bdapp\models;


use app\forms\api\LoginUserInfo;
use app\models\UserInfo;
use app\plugins\bdapp\Plugin;
use GuzzleHttp\Client;

class LoginForm extends \app\forms\api\LoginForm
{

    /**
     * @return LoginUserInfo
     */
    protected function getUserInfo()
    {
        $postData = \Yii::$app->request->post();
        $rawData = $postData['rawData'];
        $postUserInfo = json_decode($rawData, true);
        $plugin = new Plugin();
        /**@var BdappConfig $config**/
        $config = $plugin->getBdConfig();
        $client = new Client();
        $response = $client->request('post', 'https://spapi.baidu.com/oauth/jscode2sessionkey', [
            'verify' => false,
            'form_params' => [
                'code' => $postData['code'],
                'client_id' => $config->app_key,
                'sk' => $config->app_secret,
            ],
        ]);
        $resultJson = $response->getBody()->getContents();
        $tokenData = json_decode($resultJson, true);

        $userInfo = new LoginUserInfo();
        $userInfo->username = $tokenData['openid'];
        $userInfo->nickname = $postUserInfo['nickName'];
        $userInfo->avatar = $postUserInfo['avatarUrl'];
        $userInfo->platform_user_id = $tokenData['openid'];
        $userInfo->platform = UserInfo::PLATFORM_BDAPP;

        return $userInfo;
    }
}
