<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\poster\PosterForm;

class QrcodeController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'mchLogin' => [
                'class' => LoginFilter::class,
                'ignore' => []
            ]
        ]);
    }

    public function actionGoodsNew()
    {
        $form = new PosterForm();
        return $this->asJson($form->poster('goodsNew', \Yii::$app->request->get()));
    }

    public function actionGoods()
    {
        $form = new PosterForm();
        return $this->asJson($form->poster('goods', \Yii::$app->request->get()));
    }

    public function actionShare()
    {
        $form = new PosterForm();
        return $this->asJson($form->poster('share', \Yii::$app->request->get()));
    }

    public function actionTopic()
    {
        $form = new PosterForm();
        return $this->asJson($form->poster('topic', \Yii::$app->request->get()));
    }

    public function actionFootprint()
    {
        $form = new PosterForm();
        return $this->asJson($form->poster('footprint', \Yii::$app->request->get()));
    }
}
