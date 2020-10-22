<?php

namespace app\plugins\booking\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\booking\forms\api\OrderListForm;

class OrderListController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionDetail() {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }

    public function actionClerkCode() {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->clerkCode());
    }
}
