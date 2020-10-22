<?php

namespace app\plugins\quick_share\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\quick_share\forms\api\GoodsPoster;
use app\plugins\quick_share\forms\api\PosterInfoForm;
use app\plugins\quick_share\forms\api\PosterForm;
use app\plugins\quick_share\forms\api\PosterNewForm;
use app\plugins\quick_share\forms\api\QuickShareForm;


class GoodsController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => []
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new QuickShareForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    public function actionPosterList()
    {
        $form = new GoodsPoster();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
