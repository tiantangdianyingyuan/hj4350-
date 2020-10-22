<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/5 12:09
 */


namespace app\plugins\wxapp\models;

use app\forms\api\LoginUserInfo;
use app\models\UserInfo;
use app\plugins\wxapp\Plugin;

class LoginForm extends \app\forms\api\LoginForm
{
    /**
     * @return LoginUserInfo
     * @throws \Exception
     */
    public function getUserInfo()
    {
        $scope = 'auth_info';
        /** @var Plugin $plugin */
        $plugin = new Plugin();
        $postData = \Yii::$app->request->post();
        if (isset($postData['rawData'])) {
            $rawData = $postData['rawData'];
            $postUserInfo = json_decode($rawData, true);
            $data = $plugin->getWechat()->decryptData(
                $postData['encryptedData'],
                $postData['iv'],
                $postData['code']
            );
            $openId = $data['openId'];
            $unionId = $data['unionId'] ?? '';
        } else {
            $scope = 'auth_base';
            $data = $plugin->getWechat()->jsCodeToSession($postData['code']);
            $openId = $data['openid'];
            $unionId = $data['unionid'] ?? '';
            $postUserInfo['nickName'] = '';
            $postUserInfo['avatarUrl'] = '';
        }
        $userInfo = new LoginUserInfo();
        $userInfo->username = $openId;
        $userInfo->scope = $scope;
        $userInfo->nickname = isset($data['nickName']) ? $data['nickName'] : $postUserInfo['nickName'];
        $userInfo->avatar = isset($data['avatarUrl']) ? $data['avatarUrl'] : $postUserInfo['avatarUrl'];
        $userInfo->platform_user_id = $openId;
        $userInfo->platform = UserInfo::PLATFORM_WXAPP;
        $userInfo->unionId = $unionId;
        return $userInfo;
    }
}
