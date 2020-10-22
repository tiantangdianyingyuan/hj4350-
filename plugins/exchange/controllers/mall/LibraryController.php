<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\controllers\mall;

use app\plugins\Controller;
use app\plugins\exchange\forms\mall\LibraryEditForm;
use app\plugins\exchange\forms\mall\LibraryForm;
use app\plugins\exchange\forms\mall\RecordLogForm;

class LibraryController extends Controller
{
    //v
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LibraryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    //v
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LibraryEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new LibraryForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    //v
    public function actionRecordLog()
    {
        if (\Yii::$app->request->isPost) {
            $form = new RecordLogForm();
            $form->attributes = \Yii::$app->request->post();
            $form->flag = 'EXPORT';
            return $this->asJson($form->getList());
        }
        if (\Yii::$app->request->isAjax) {
            $form = new RecordLogForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
        return $this->render('list');
    }

    //v
    public function actionRecycle()
    {
        if (\Yii::$app->request->isPost) {
            $form = new LibraryForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->recycle());
        }
    }

    //v
    public function actionDestory()
    {
        if (\Yii::$app->request->isPost) {
            $form = new LibraryForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destory());
        }
    }
}
