<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin;


class IndexController extends AdminController
{
    public function actionIndex()
    {
        return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['admin/user/me']));
    }

    public function actionTest()
    {
        return $this->render('test');
    }

    public function actionBackIndex()
    {
    	\Yii::$app->removeSessionMallId();
        return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['admin/user/me']));
    }
}
