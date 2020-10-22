<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\core\response\ApiCode;
use app\forms\permission\menu\MenusForm;

class MenusController extends MallController
{
    public function actionIndex()
    {
        $route = \Yii::$app->request->post('route');

        $form = new MenusForm();
        $form->currentRoute = $route;
        $res = $form->getMenus('mall');

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'menus' => $res['menus'],
                'currentRouteInfo' => $res['currentRouteInfo'],
                'courseMenu' => $res['courseMenu']
            ]
        ]);
    }

    public function actionPlugin($name)
    {
        \Yii::$app->plugin->setCurrentPlugin(\Yii::$app->plugin->getPlugin($name));
        $route = \Yii::$app->request->post('route');

        $form = new MenusForm();
        $form->currentRoute = $route;
        $res = $form->getMenus('plugin');

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'menus' => $res['menus'],
                'currentRouteInfo' => $res['currentRouteInfo']
            ]
        ]);
    }
}
