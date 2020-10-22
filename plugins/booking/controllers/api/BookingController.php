<?php

namespace app\plugins\booking\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\booking\forms\api\BookingForm;
use app\plugins\booking\forms\api\PosterForm;

class BookingController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['setting', 'store-list', 'cats']
            ],
        ]);
    }
    
    //分类列表v
    public function actionCats()
    {
        $form = new BookingForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->cats());
    }

    /**
     * 门店列表v
     */
    public function actionStoreList()
    {
        $form = new BookingForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->store());
    }

    public function actionSetting()
    {
        $form = new BookingForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->setting());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
