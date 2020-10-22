<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\forms\mall\navbar\NavbarEditForm;
use app\forms\mall\navbar\NavbarForm;
use app\forms\mall\order_form\OrderFormEditForm;
use app\forms\mall\order_form\OrderFormForm;
use app\forms\mall\order_form\OrderFormUpdate;

class OrderFormController extends MallController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new OrderFormForm();
                $form->id = \Yii::$app->request->get('id');
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new OrderFormEditForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderFormForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    public function actionUpdate()
    {
        $form = new OrderFormUpdate();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionAllList()
    {
        $form = new OrderFormForm();
        return $this->asJson($form->getAllList());
    }
}
