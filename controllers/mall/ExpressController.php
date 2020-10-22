<?php
/**
* link: http://www.zjhejiang.com/
* copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
* author: xay
*/

namespace app\controllers\mall;

use app\forms\mall\express\ExpressEditForm;
use app\forms\mall\express\SenderOptionForm;
use app\forms\mall\express\ExpressForm;

class ExpressController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ExpressForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ExpressEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new ExpressForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new ExpressForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    public function actionDefaultSender()
    {
        if (\Yii::$app->request->isPost) {
            $form = new SenderOptionForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionExpressList()
    {
        $form = new ExpressForm();
        return $this->asJson($form->getExpressList());
    }
}
