<?php
namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\cart\CartAddForm;
use app\forms\api\cart\CartDeleteForm;
use app\forms\api\cart\CartEditForm;
use app\forms\api\cart\CartForm;

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
        if (\Yii::$app->request->isPost) {
            $form = new CartAddForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CartEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionDelete()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CartDeleteForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }
}
