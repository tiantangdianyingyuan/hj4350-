<?php

namespace app\plugins\gift\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\gift\forms\api\PosterForm;
use app\plugins\gift\forms\api\GiftForm;
use app\plugins\gift\forms\api\GiftSettingForm;

class GiftIndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionGift()
    {
        $form = new GiftForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionConfig()
    {
        $form = new GiftSettingForm();
        return $this->asJson($form->getList());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
