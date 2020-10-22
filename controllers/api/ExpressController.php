<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\api;

use app\forms\api\DeliveryForm;
use app\forms\api\ExpressForm;

class ExpressController extends ApiController
{
    public function actionExpressList()
    {
        $form = new ExpressForm();
        return $this->asJson($form->getExpressList());
    }

    public function actionGetCustomer()
    {
        $form = new ExpressForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCustomer());
    }

    public function actionDeliveryPrice()
    {
        $form = new DeliveryForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getPrice());
    }

    public function actionDeliveryConfig()
    {
        $form = new DeliveryForm();
        return $this->asJson($form->getConfig());
    }

    public function actionOrderExpressList()
    {
        $form = new ExpressForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->orderExpressList());
    }
}
