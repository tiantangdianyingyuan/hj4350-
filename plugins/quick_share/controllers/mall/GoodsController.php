<?php

namespace app\plugins\quick_share\controllers\mall;

use app\plugins\Controller;
use app\plugins\quick_share\forms\mall\GoodsEditForm;
use app\plugins\quick_share\forms\mall\GoodsForm;

class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->get('search');
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionSearch()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->get('search');
        return $this->asJson($form->getSearch());
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = \yii\helpers\BaseJson::decode($data['form']);
                $form->attributes = \yii\helpers\BaseJson::decode($data['form'])['detail'];
                //$form->attributes = \yii\helpers\BaseJson::decode($data['attrGroups']);
                return $this->asJson($form->save());
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    public function actionBatchDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchDestroy());
        }
    }

    public function actionEditAlone()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editAlone());
        }
    }
}