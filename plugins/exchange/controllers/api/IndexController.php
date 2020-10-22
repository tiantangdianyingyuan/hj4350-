<?php

namespace app\plugins\exchange\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\exchange\forms\api\CardGoodsForm;
use app\plugins\exchange\forms\api\CodeForm;
use app\plugins\exchange\forms\api\COrderSubmitForm;
use app\plugins\exchange\forms\api\EOrderSubmitForm;
use app\plugins\exchange\forms\api\ExchangePosterForm;
use app\plugins\exchange\forms\api\MeCardForm;
use app\plugins\exchange\forms\api\RecordForm;
use app\plugins\exchange\forms\api\SettingForm;

class IndexController extends ApiController
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
    //v
    public function actionCardGoodsList()
    {
        $form = new CardGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->list());
    }

    //v
    public function actionCardGoodsDetail()
    {
        $form = new CardGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }
    //v
    public function actionEOrderPreview()
    {
        $form = new EOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->getConfig()->preview());
    }
    //v
    public function actionEOrderSubmit()
    {
        $form = new EOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->getConfig()->submit());
    }
    //t
    public function actionMeCardList()
    {
        $form = new MeCardForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->list());
    }
    //t
    public function actionMeCardDetail()
    {
        $form = new MeCardForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }
    //v
    public function actionSetting()
    {
        $form = new SettingForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->get());
    }
    //v
    public function actionMeLog()
    {
        $form = new CodeForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->log());
    }
    //v
    public function actionMeLogDetail()
    {
        $form = new CodeForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }
    //v
    public function actionShowInfo()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new RecordForm();
            $form->code = \Yii::$app->request->get('code');
            $this->asJson($form->showInfo());
        }
    }
    //v
    public function actionUnite()
    {
        if (\Yii::$app->request->isPost) {
            $form = new RecordForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->unite());
        }
    }
    //v
    public function actionCovert()
    {
        if (\Yii::$app->request->isPost) {
            $form = new RecordForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->convert());
        }
    }
    public function actionCOrderPreview()
    {
        $form = new COrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->getConfig()->preview());
    }

    public function actionCOrderSubmit()
    {
        $form = new COrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->getConfig()->submit());
    }

    public function actionQrcode()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ExchangePosterForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->poster());
        }
    }
}
