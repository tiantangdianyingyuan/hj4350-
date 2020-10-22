<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall\filters;

use app\forms\common\CommonAuth;
use app\forms\common\CommonUser;
use yii\base\ActionFilter;
use Yii;

class PermissionsBehavior extends ActionFilter
{
    /**
     * 安全路由，权限验证时会排除这些路由
     * @var array
     */
    private $safeRoute = [
        'mall/index/index',
        'mall/error/permission'
    ];
    /**
     * 超级管理员路由，仅超级管理员可访问
     * @var array
     */
    private $superRoute = [
        'mall/plugin/detail',
    ];

    public function beforeAction($action)
    {
        if (\Yii::$app->user->isGuest == false) {
            //路由名称
            $route = Yii::$app->requestedRoute;
            //排除安全路由
            if (in_array($route, $this->safeRoute)) {
                return true;
            }

            // TODO 异步请求不验证
            if (Yii::$app->request->isAjax) {
                return true;
            }

            // 超级管理员无需验证
            $userIdentity = CommonUser::getUserIdentity();
            if ($userIdentity->is_super_admin == 1) {
                return true;
            }

            if (in_array($route, $this->superRoute)) {
                $this->permissionError();
            }

            // 子账号管理员
            if ($userIdentity->is_admin == 1) {
                $notPermissionRoutes = CommonAuth::getPermissionsRouteList();

                if (in_array($route, $notPermissionRoutes)) {
                    $this->permissionError();
                }
                return true;
            }

            $userPermissions = [];
            // 判断操作员权限
            if ($userIdentity->is_operator == 1) {
                $userPermissions = CommonUser::getUserPermissions();
            }
            // 多商户
            if (\Yii::$app->user->identity->mch_id) {
                $userPermissions = CommonUser::getMchPermissions();
            }

            if (!in_array($route, $userPermissions)) {
                $this->permissionError();
            }
        }

        return true;
    }

    public function permissionError()
    {
        $response = Yii::$app->getResponse();
        $response->data = Yii::$app->controller->renderFile('@app/views/error/permission.php');
        $response->send();
    }
}
