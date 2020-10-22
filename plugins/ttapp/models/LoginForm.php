<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/8/2
 * Time: 14:31
 */

namespace app\plugins\ttapp\models;


use app\forms\api\LoginUserInfo;
use app\models\UserInfo;
use app\plugins\ttapp\Plugin;
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
        /**@var TtappConfig $config**/
        $config = $plugin->getTtConfig();
        $client = new Client();
        $response = $client->request('get', 'https://developer.toutiao.com/api/apps/jscode2session', [
            'verify' => false,
            'query' => [
                'appid' => $config->app_key,
                'secret' => $config->app_secret,
                'code' => $postData['code'],
            ],
        ]);
        $resultJson = $response->getBody()->getContents();
        $tokenData = json_decode($resultJson, true);

        $userInfo = new LoginUserInfo();
        $userInfo->username = $tokenData['openid'];
        $userInfo->nickname = $postUserInfo['nickName'];
        $userInfo->avatar = $postUserInfo['avatarUrl'];
        $userInfo->platform_user_id = $tokenData['openid'];
        $userInfo->platform = UserInfo::PLATFORM_TTAPP;

        return $userInfo;
    }
}
