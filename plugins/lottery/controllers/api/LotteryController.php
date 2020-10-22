<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\controllers\api;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\lottery\forms\api\LotteryDetailForm;
use app\plugins\lottery\forms\api\LotteryForm;
use app\plugins\lottery\forms\api\LotteryLogForm;
use app\plugins\lottery\forms\api\LotteryOrderSubmitForm;
use app\plugins\lottery\forms\api\LotteryPosterForm;
use app\plugins\lottery\forms\common\CommonLottery;

class LotteryController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'setting', 'goods']
            ],
        ]);
    }
    //首页
    public function actionIndex()
    {
        $form = new LotteryForm();
        return $this->asJson($form->search());
    }

    //抽奖规则
    public function actionSetting()
    {
        $form = new LotteryForm();
        return $this->asJson($form->setting());
    }

    public function actionDetail()
    {
        $form = new LotteryDetailForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->detail());
    }

    public function actionPrize()
    {
        $form = new LotteryLogForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionCode()
    {
        $form = new LotteryLogForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->luckyCode());
    }

    public function actionClerk()
    {
        $form = new LotteryDetailForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->clerk());
    }

    public function actionGoods()
    {
        $form = new LotteryDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->goods());
    }

    //海报
    public function actionPoster()
    {
        $form = new LotteryPosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    public function actionOrderPreview()
    {
        $form = new LotteryOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));

        $setting = CommonLottery::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('lottery')
            ->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new LotteryOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        $setting = CommonLottery::getSetting();
        if ($setting) {
            $paymentType = $setting['payment_type'];
        } else {
            $paymentType = ['online_pay'];
        }
        return $this->asJson($form->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($paymentType)
            ->setSign('lottery')
            ->submit());
    }
}
