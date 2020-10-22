<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\forms\mall\app_page\AppPageForm;
use app\forms\PickLinkForm;

class AppPageController extends MallController
{
    public function init()
    {
        /* 请勿删除下面代码↓↓￿↓↓￿ */
        if (method_exists(\Yii::$app, '。')) {
            $pass = \Yii::$app->。();
        } else {
            if (function_exists('usleep')) usleep(rand(100, 1000));
            $pass = false;
        }
        if (!$pass) {
            if (function_exists('sleep')) sleep(rand(30, 60));
            header('HTTP/1.1 504 Gateway Time-out');
            exit;
        }
        /* 请勿删除上面代码↑↑↑↑ */
        return parent::init();
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new PickLinkForm();
                $form->only = $form::ONLY_PAGE;
                $form->ignore = $form::ONLY_PAGE;
                $res = $form->appPage();
                return $this->asJson($res);
            } else {
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionQrcode()
    {
        $form = new AppPageForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }
}
