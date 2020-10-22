<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/5 15:00
 */


namespace app\forms\api;


use app\events\UserEvent;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;

abstract class LoginForm extends Model
{
    /**
     * @return LoginUserInfo
     */
    abstract protected function getUserInfo();

    public function login()
    {
        $userInfo = $this->getUserInfo();
        $user = User::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'username' => $userInfo->username,
            'is_delete' => 0,
        ])->one();
        if ($userInfo->scope == 'auth_base') {
            if ($user) {
                return [
                    'code' => 0,
                    'data' => [
                        'access_token' => $user->access_token,
                    ],
                ];
            } else {
                return [
                    'code' => 1,
                    'data' => [
                        'access_token' => null,
                    ],
                ];
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        $register = false;
        if (!$user) {
            $register = true;
            $user = new User();
            $user->mall_id = \Yii::$app->mall->id;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->username = $userInfo->username;
            $user->nickname = $userInfo->nickname;
            $user->unionid = $userInfo->unionId;
            $user->password = \Yii::$app->security
                ->generatePasswordHash(\Yii::$app->security->generateRandomString(), 5);

            if (!$user->save()) {
                $t->rollBack();
                return $this->getErrorResponse($user);
            }
        } else {
            $user->unionid = $userInfo->unionId;
            $user->nickname = $userInfo->nickname;
            $user->save();
        }

        // 用户信息表
        $uInfo = UserInfo::findOne([
            'user_id' => $user->id,
            'is_delete' => 0,
        ]);
        if (!$uInfo) {
            $uInfo = new UserInfo();
            $uInfo->user_id = $user->id;
            $uInfo->avatar = $userInfo->avatar;
            $uInfo->platform_user_id = $userInfo->platform_user_id;
            $uInfo->platform = $userInfo->platform;
            $uInfo->is_delete = 0;
        } else {
            $uInfo->avatar = $userInfo->avatar;
        }
        if (!$uInfo->save()) {
            $t->rollBack();
            return $this->getErrorResponse($uInfo);
        }

        // 用户角色表
        $userIdentity = UserIdentity::findOne([
            'user_id' => $user->id,
            'is_delete' => 0
        ]);
        if (!$userIdentity) {
            $userIdentity = new UserIdentity();
            $userIdentity->user_id = $user->id;
        }
        if (!$userIdentity->save()) {
            $t->rollBack();
            return $this->getErrorMsg($userIdentity);
        }
        $t->commit();

        $event = new UserEvent();
        $event->sender = $this;
        $event->user = $user;
        if ($register) {
            \Yii::$app->trigger(User::EVENT_REGISTER, $event);
        }
        \Yii::$app->trigger(User::EVENT_LOGIN, $event);

        return [
            'code' => 0,
            'data' => [
                'access_token' => $user->access_token,
            ],
        ];
    }
}
