<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/13 11:59
 */


namespace app\plugins\aliapp\models;


use Alipay\AlipayRequestFactory;
use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\forms\api\LoginUserInfo;
use app\models\AliappConfig;
use app\models\UserInfo;

class LoginForm extends \app\forms\api\LoginForm
{

    /**
     * @return LoginUserInfo
     * @throws \Alipay\Exception\AlipayErrorResponseException
     */
    protected function getUserInfo()
    {
        $aliappConfig = AliappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$aliappConfig) {
            throw new \Exception('后台小程序尚未配置。');
        }

        $postData = \Yii::$app->request->post();
        $rawData = $postData['rawData'];
        $postUserInfo = json_decode($rawData, true);

        $aop = new AopClient(
            $aliappConfig->appid,
            AlipayKeyPair::create($aliappConfig->app_private_key, $aliappConfig->alipay_public_key)
        );

        $tokenRequest = AlipayRequestFactory::create('alipay.system.oauth.token', [
            'grant_type' => 'authorization_code',
            'code' => \Yii::$app->request->post('code'),
        ]);
        $tokenData = $aop->execute($tokenRequest)->getData();

        $defaultAvatar = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/user-default-avatar.png';

        $userInfo = new LoginUserInfo();
        $userInfo->username = $tokenData['user_id'];
        $userInfo->nickname = empty($postUserInfo['nickName']) ? $userInfo->username : $postUserInfo['nickName'];
        $userInfo->avatar = empty($postUserInfo['avatar']) ? $defaultAvatar : $postUserInfo['avatar'];
        $userInfo->platform_user_id = $tokenData['user_id'];
        $userInfo->platform = UserInfo::PLATFORM_ALIAPP;

        return $userInfo;
    }
}
