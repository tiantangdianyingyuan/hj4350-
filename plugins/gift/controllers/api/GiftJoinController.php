<?php

namespace app\plugins\gift\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\gift\forms\api\GiftJoinForm;
use app\plugins\gift\forms\api\GiftListForm;

class GiftJoinController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionJoin()
    {
        $form = new GiftJoinForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->join());
    }

    public function actionJoinStatus()
    {
        $form = new GiftJoinForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->joinStatus());
    }

    //我参与的
    public function actionMyJoin()
    {
        $form = new GiftListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getJoinList());
    }

    public function actionJoinDetail()
    {
        $form = new GiftListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getJoinList());
    }

    //我收到的
    public function actionMyWin()
    {
        $form = new GiftListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getMyList());
    }

    public function actionWinDetail()
    {
        $form = new GiftListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getMyList());
    }
}
