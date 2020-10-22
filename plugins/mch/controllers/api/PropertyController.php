<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\api;


use app\forms\mall\mch\CashEditForm;
use app\plugins\mch\forms\api\PropertyForm;

class PropertyController extends ApiController
{
    public function actionIndex()
    {
        $form = new PropertyForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->search());
    }

    public function actionCashSubmit()
    {
        $form = new CashEditForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->save());
    }

    public function actionAccountLog()
    {
        $form = new PropertyForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getAccountLog());
    }

    public function actionCashLog()
    {
        $form = new PropertyForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getCashLog());
    }

    public function actionOrderCloseLog()
    {
        $form = new PropertyForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getOrderCloseLog());
    }
}
