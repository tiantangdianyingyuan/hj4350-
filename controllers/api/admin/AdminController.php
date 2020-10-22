<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/30
 * Time: 9:11
 */

namespace app\controllers\api\admin;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\core\cloud\CloudException;
use app\core\exceptions\ClassNotFoundException;
use app\core\response\ApiCode;
use app\models\UserIdentity;
use yii\web\NotFoundHttpException;

/**
 * 小程序管理后台基类
 * Class AdminController
 * @package app\controllers\api\admin
 */
class AdminController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function beforeAction($action)
    {
        $user_id = \Yii::$app->user->id;
        if (empty($user_id)) {
            throw new NotFoundHttpException('非法操作');
        }

        try {
            \Yii::$app->plugin->getPlugin('app_admin');
        } catch (ClassNotFoundException | CloudException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }

        $identity = UserIdentity::find()->where([
            'user_id' => $user_id,
            'is_admin' => 1,
            'is_delete' => 0
        ])->one();
        if (empty($identity)) {
            throw new NotFoundHttpException('该帐号无管理员权限');
        }

        $permission = \Yii::$app->role->permission;

        if (!in_array('app_admin',$permission)) {
            throw new NotFoundHttpException('该帐号无此插件权限');
        }

        return parent::beforeAction($action);
    }

}