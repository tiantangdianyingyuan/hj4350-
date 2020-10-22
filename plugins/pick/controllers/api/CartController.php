<?php

namespace app\plugins\pick\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\pick\forms\api\CartAddForm;
use app\plugins\pick\forms\api\CartDeleteForm;
use app\plugins\pick\forms\api\CartEditForm;
use app\plugins\pick\forms\api\CartForm;

class CartController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['list', 'edit']
            ],
        ]);
    }

    public function actionList()
    {
        $form = new CartForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->search();
    }

    public function actionAdd()
    {
        $form = new CartAddForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->save();
    }

    public function actionEdit()
    {
        $form = new CartEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionDelete()
    {
        $form = new CartDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $form->save();
    }
}
