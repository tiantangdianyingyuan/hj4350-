<?php

namespace app\controllers\mall;

use app\forms\mall\cat\QuickShopCatForm;

class QuickShopController extends MallController
{

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionEditSort()
    {
        if (\Yii::$app->request->isPost) {
            $form = new QuickShopCatForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editSort());
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new QuickShopCatForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }
}
