<?php
/**
* link: http://www.zjhejiang.com/
* copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
* author: xay
*/

namespace app\controllers\mall;

use app\forms\mall\printer\PrinterForm;
use app\forms\mall\printer\PrinterSettingForm;
use app\forms\mall\printer\PrinterSettingEditForm;
use app\forms\mall\printer\PrinterEditForm;

class PrinterController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrinterForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new PrinterForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PrinterEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new PrinterForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrinterSettingForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('setting');
        }
    }

    public function actionSettingDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new PrinterSettingForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    public function actionSettingEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PrinterSettingEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new PrinterSettingForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('setting');
        }
    }
}
