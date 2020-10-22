<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin\behaviors;

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
    private $safeRoute = [];

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

            // 多商户
            if (Yii::$app->user->identity->mch_id) {
                $this->mch($route);
            }

            // 超级管理员无需验证
            $userIdentity = CommonUser::getUserIdentity();
            if ($userIdentity->is_super_admin == 1) {
                return true;
            }

            // 子账号管理员
            if ($userIdentity->is_admin == 1) {
                $notPermissionRoutes = CommonAuth::getPermissionsRouteList();
                if (in_array($route, $notPermissionRoutes)) {
                    \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl('/admin/user/me'))->send();
                }
                return true;
            }
        }

        return true;
    }

    public function mch($route)
    {
        $mchAuthRoute = CommonUser::getMchPermissions();

        if (in_array($route, $mchAuthRoute)) {
            return true;
        }

        \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl('/mall/index/index'))->send();
    }
}
