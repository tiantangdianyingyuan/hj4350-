<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\controllers\mall;


use app\plugins\Controller;
use app\plugins\scan_code_pay\forms\mall\ActivityEditForm;
use app\plugins\scan_code_pay\forms\mall\ActivityForm;

class ActivityController extends Controller
{

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ActivityForm();
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
                $form = new ActivityEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new ActivityForm();
                $form->attributes = \Yii::$app->request->get();

                return $this->asJson($form->detail());
            }

        } else {
            return $this->render('edit');
        }
    }

    public function actionSwitchStatus()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->switchStatus());
    }

    public function actionDestroy()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->destroy());
    }

    public function actionMembers()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getMembers());
    }

    public function actionCoupons()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCoupons());
    }

    public function actionCards()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCards());
    }
}