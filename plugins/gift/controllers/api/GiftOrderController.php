<?php

namespace app\plugins\gift\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\gift\forms\api\GiftConvertSubmitForm;
use app\plugins\gift\forms\api\GiftOrderCanceledForm;
use app\plugins\gift\forms\api\GiftOrderPayForm;
use app\plugins\gift\forms\api\GiftOrderSubmitForm;
use app\plugins\gift\forms\api\GiftTurnForm;

class GiftOrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionOrderPreview()
    {
        $form = new GiftOrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new GiftOrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->submit());
    }

    public function actionOrderCancel()
    {
        $form = new GiftOrderCanceledForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    public function actionGiftConvertPreview()
    {
//        return $this->asJson(
//            ['code' => 0,
//                'data' => [
//                    'send_type' => (CommonGift::getSetting())['send_type']
//                ]
//            ]
//        );
        $form = new GiftConvertSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->preview());
    }

    public function actionGiftConvert()
    {
        $form = new GiftConvertSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->submit());
    }

    public function actionPayData()
    {
        $form = new GiftOrderPayForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }

    //转赠
    public function actionTurn()
    {
        $form = new GiftTurnForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->turn());
    }

    //接受
    public function actionGetTurn()
    {
        $form = new GiftTurnForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->get_turn());
    }
}
