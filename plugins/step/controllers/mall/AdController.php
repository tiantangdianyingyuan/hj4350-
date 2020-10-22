<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\controllers\mall;

use app\plugins\Controller;
use app\plugins\step\forms\mall\AdForm;
use app\plugins\step\forms\mall\AdEditForm;

class AdController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AdForm();
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
                $form = new AdEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new AdForm();
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
            $form = new AdForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editStatus());
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new AdForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }
}
