<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\controllers\mall;

use app\plugins\Controller;
use app\plugins\step\forms\mall\UserForm;

class UserController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    //兑换记录
    public function actionLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getLog());
        } else {
            return $this->render('log');
        }
    }

    //邀请记录
    public function actionInvite()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->invite());
        }
    }

    //步数币修改
    public function actionEditCurrency()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->currency());
        }
    }

    public function actionBatchCurrency()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchCurrency());
        }
    }
    //清空步数币
    public function actionDestroyCurrency()
    {
        if(\Yii::$app->request->isPost) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroyCurrency());
        }
    }
}
