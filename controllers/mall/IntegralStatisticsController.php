<?php


namespace app\controllers\mall;


use app\forms\mall\statistics\IntegralForm;

class IntegralStatisticsController extends MallController
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
            $form = new IntegralForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new IntegralForm();
                $form->attributes = \Yii::$app->request->post();
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    public function actionMall()
    {
        if (\Yii::$app->request->isAjax) {
            $plugin = \Yii::$app->plugin->getPlugin('integral_mall');
            $form = $plugin->getApi();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $plugin = \Yii::$app->plugin->getPlugin('integral_mall');
                $form = $plugin->getApi();
                $form->attributes = \Yii::$app->request->post();
                $form->search();
                return false;
            } else {
                return $this->render('mall');
            }
        }
    }
}