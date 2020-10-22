<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\controllers\mall;

use app\plugins\Controller;
use app\plugins\step\forms\mall\ActivityForm;
use app\plugins\step\forms\mall\ActivityEditForm;

class ActivityController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new ActivityForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionEditStatus()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editStatus());
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new ActivityForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }
    
    //参与名单
    public function actionPartakeList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->partakeList());
        } else {
            return $this->render('partake-list');
        }
    }
    //解散活动
    public function actionDisband()
    {
        if (\Yii::$app->request->isPost) {
            $form = new ActivityForm();
            //$form->id  =3;
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->disband());
        }
    }
}
