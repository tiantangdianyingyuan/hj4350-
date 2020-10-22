<?php

namespace app\plugins\step\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\step\forms\api\AdRewardForm;
use app\plugins\step\forms\api\PosterForm;
use app\plugins\step\forms\api\StepActivityForm;
use app\plugins\step\forms\api\StepActivitySubmitForm;
use app\plugins\step\forms\api\StepDailyForm;
use app\plugins\step\forms\api\StepGoodsForm;
use app\plugins\step\forms\api\StepIndexForm;
use app\plugins\step\forms\api\StepOrderSubmitForm;
use app\plugins\step\forms\api\StepPosterForm;
use app\plugins\step\forms\api\StepUserForm;
use app\plugins\step\forms\common\CommonStep;

class StepController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'setting', 'activity', 'goods', 'goods-detail']
            ],
        ]);
    }

    //首页v
    public function actionIndex()
    {
        $form = new StepIndexForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->index());
    }

    //配置v
    public function actionSetting()
    {
        $form = new StepIndexForm();
        return $this->asJson($form->setting());
    }

    //活动v
    public function actionActivity()
    {
        $form = new StepActivityForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    //活动详情v
    public function actionActivityDetail()
    {
        $form = new StepActivityForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }

    //活动记录v
    public function actionActivityLog()
    {
        $form = new StepActivityForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getLog());
    }

    //参加活动v
    public function actionActivityJoin()
    {
        $form = new StepActivityForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->join());
    }

    //活动提交v
    public function actionActivitySubmit()
    {
        $form = new StepActivitySubmitForm();
        //$form->activity_id = 1;
        //$form->num = 12;
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->submit());
    }

    //邀请列表v
    public function actionInviteList()
    {
        $form = new StepUserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->inviteList());
    }

    //用户提醒v
    public function actionRemind()
    {
        $form = new StepUserForm();
        //$form->is_remind = 2;
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->remind());
    }

    //排行v
    public function actionRanking()
    {
        $form = new StepUserForm();
        //$form->status = 2;
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->ranking());
    }

    //用户记录v
    public function actionLog()
    {
        $form = new StepUserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getLog());
    }

    //兑换v
    public function actionConvert()
    {
        $form = new StepDailyForm();
        //$form->num = 12;
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    //商品列表v
    public function actionGoods()
    {
        $form = new StepGoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    //商品详情v
    public function actionGoodsDetail()
    {
        $form = new StepGoodsForm();
        //$form->id = 1;
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    //
    public function actionStepConvert()
    {
        $form = new StepUserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->stepConvert());
    }

    //商品海报
    public function actionGoodsPoster(){
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    //步数海报
    public function actionPoster(){
        $form = new StepPosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    //流量主领取
    public function actionReceive(){
        $form = new AdRewardForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->receive());
    }


    public function actionOrderPreview()
    {
        $form = new StepOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonStep::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }

        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableAddressEnable(CommonStep::getSetting()['is_territorial_limitation'] == 1)
            ->setEnableOrderForm(false)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('step')
            ->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new StepOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonStep::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setEnableAddressEnable(CommonStep::getSetting()['is_territorial_limitation'] == 1)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('step')
            ->submit());
    }
}
