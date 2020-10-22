<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\cart\CartEditForm;
use app\forms\api\QuickForm;

class QuickController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'goods-list']
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new QuickForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionGoodsList()
    {
        $form = new QuickForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->goodsList());
    }

    public function actionCart()
    {
        $form = new CartEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}
