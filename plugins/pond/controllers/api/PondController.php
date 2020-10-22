<?php

namespace app\plugins\pond\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\pond\forms\api\PondForm;
use app\plugins\pond\forms\api\PondLogForm;
use app\plugins\pond\forms\api\PondOrderSubmitForm;
use app\controllers\api\ApiController;
use app\plugins\pond\forms\api\PondPosterForm;
use app\plugins\pond\forms\common\CommonPond;

class PondController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'setting']
            ],
        ]);
    }

    //奖品列表
    public function actionIndex()
    {
        $form = new PondForm();
        return $this->asJson($form->index());
    }

    //立即抽奖
    public function actionLottery()
    {
        $form = new PondForm();
        return $this->asJson($form->lottery());
    }

    //抽奖规则
    public function actionSetting()
    {
        $form = new PondForm();
        return $this->asJson($form->setting());
    }

    //中奖记录
    public function actionPrize()
    {
        $form = new PondLogForm();
        return $this->asJson($form->search());
    }

    //兑换商品
    private function actionSend()
    {
        $form = new PondForm();
        return $this->asJson($form->send($pongLog));
    }

    //海报
    public function actionPoster()
    {
        $form = new PondPosterForm();
        return $this->asJson($form->poster());
    }
    
    public function actionOrderPreview()
    {
        $form = new PondOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonPond::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }

        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(true)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('pond')
            ->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new PondOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonPond::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(true)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('pond')
            ->submit());
    }
}
