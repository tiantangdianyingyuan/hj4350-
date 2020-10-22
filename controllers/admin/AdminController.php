<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 18:17
 */


namespace app\controllers\admin;


use app\controllers\admin\behaviors\PermissionsBehavior;
use app\controllers\admin\behaviors\RoleUserBehavior;
use app\controllers\behaviors\LoginFilter;
use app\controllers\Controller;

class AdminController extends Controller
{
    public function init()
    {
        // \Yii::$app->validateCloudFile();
        parent::init();
        if (property_exists(\Yii::$app, 'appIsRunning') === false) {
            exit('property not found.');
        }
    }

    public $layout = 'admin';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
                'safeRoutes' => [
                    'admin/passport/login',
                    'admin/passport/mch-login',
                    'admin/passport/logout',
                    'admin/passport/register',
                    'admin/passport/sms-captcha',
                    'admin/passport/edit-password',
                    'admin/passport/check-user-exists',
                    'admin/passport/send-reset-password-captcha',
                    'admin/passport/reset-password',
                    'admin/passport/login-qr-code',
                    'admin/passport/check-mch-login',
                    'admin/passport/mch-setting',
                    'admin/passport/role-setting',
                ],
            ],
            'adminPermissions' => [
                'class' => PermissionsBehavior::class,
            ],
        ]);
    }
}
