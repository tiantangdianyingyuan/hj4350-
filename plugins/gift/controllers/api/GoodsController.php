<?php

namespace app\plugins\gift\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\forms\api\GoodsListForm;
use app\plugins\gift\forms\api\GoodsForm;

class GoodsController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionGoodsList()
    {
        $form = new GoodsForm();
        $form->sort = \Yii::$app->request->get('sort');
        $form->sort_type = \Yii::$app->request->get('sort_type');
        $form->keyword = \Yii::$app->request->get('keyword');
        $form->page = \Yii::$app->request->get('page');
        return $form->search();
    }

    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }
}
