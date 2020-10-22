<?php
namespace app\plugins\scratch\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\scratch\forms\api\ScratchForm;
use app\plugins\scratch\forms\api\ScratchLogForm;
use app\plugins\scratch\forms\api\ScratchOrderSubmitForm;
use app\controllers\api\ApiController;
use app\plugins\scratch\forms\api\ScratchPosterForm;
use app\plugins\scratch\forms\common\CommonScratch;

class ScratchController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'setting', 'record']
            ],
        ]);
    }

    //奖品列表
    public function actionIndex()
    {
        $form = new ScratchForm();
        return $this->asJson($form->index());
    }

    //领取奖品
    public function actionReceive()
    {
        $form = new ScratchForm();
        $form->id = \Yii::$app->request->get('id');
        return $this->asJson($form->receive());
    }

    //抽奖规则
    public function actionSetting()
    {
        $form = new ScratchForm();
        return $this->asJson($form->setting());
    }

    //中奖记录
    public function actionPrize()
    {
        $form = new ScratchLogForm();
        return $this->asJson($form->search());
    }
    //
    public function actionRecord()
    {
        $form = new ScratchLogForm();
        return $this->asJson($form->record());
    }
    
    public function actionOrderPreview()
    {
        $form = new ScratchOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonScratch::getSetting();
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
            ->setSign('scratch')
            ->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new ScratchOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonScratch::getSetting();
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
            ->setSign('scratch')
            ->submit());
    }

    //海报
    public function actionPoster()
    {
        $form = new ScratchPosterForm();
        return $this->asJson($form->poster());
    }
}
