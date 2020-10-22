<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;

use app\forms\mall\clerk\ClerkCardForm;
use app\forms\mall\clerk\ClerkOrderForm;

class ClerkController extends MallController
{
    public function actionOrder()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkOrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getList());
        } else {
            return $this->render('order');
        }
    }


    public function actionCard()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ClerkCardForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getList());
        } else {
            return $this->render('card');
        }
    }
}
