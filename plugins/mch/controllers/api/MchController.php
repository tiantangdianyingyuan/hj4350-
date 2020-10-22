<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\mch\controllers\api\filter\MchLoginFilter;
use app\plugins\mch\forms\api\MchEditForm;
use app\plugins\mch\forms\api\MchForm;
use app\plugins\mch\forms\api\MchManageForm;
use app\plugins\mch\forms\api\MchPassportForm;
use app\plugins\mch\forms\api\MchQrCodeLoginForm;
use app\plugins\mch\forms\api\PosterForm;
use app\plugins\mch\forms\api\UpdatePasswordForm;
use app\plugins\mch\forms\api\VisitLogEditForm;

class MchController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index', 'category', 'detail','setting', 'add-visit']
            ],
            'mchLogin' => [
                'class' => MchLoginFilter::class,
                'ignore' => [
                    'plugin/mch/api/mch/login',
                    'plugin/mch/api/mch/qr-code-login',
                    'plugin/mch/api/mch/category',
                    'plugin/mch/api/mch/mch-status',
                    'plugin/mch/api/mch/detail',
                    'plugin/mch/api/mch/setting',
                    'plugin/mch/api/mch/index',
                    'plugin/mch/api/mch/category',
                    'plugin/mch/api/mch/mch-status',
                    'plugin/mch/api/mch/add-visit',
                    'plugin/mch/api/mch/apply',
                    'plugin/mch/api/mch/update-password',
                    'plugin/mch/api/mch/poster',
                    'plugin/mch/api/mch/year-list',
                    'plugin/mch/api/mch/current-mch-setting',
                ]
            ]
        ]);
    }

    public function actionIndex()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionCategory()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCategory());
    }

    public function actionSetting()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->setting());
    }

    public function actionEdit()
    {
        $form = new MchEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionApply()
    {
        $form = new MchEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionMchStatus()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->mchStatus());
    }

    public function actionAddVisit()
    {
        $form = new VisitLogEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionManageIndex()
    {
        $form = new MchManageForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionQrCode()
    {
        $form = new MchManageForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getQrCode());
    }

    public function actionStatistic()
    {
        $form = new MchManageForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getStatistic());
    }

    public function actionYearList()
    {
        $form = new MchManageForm();
        $form->mch_id = 0;
        return $this->asJson($form->getYearList());
    }

    public function actionLogin()
    {
        $form = new MchPassportForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->login());
    }

    public function actionQrCodeLogin()
    {
        $form = new MchQrCodeLoginForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->qrCodeLogin());
    }

    public function actionUpdatePassword()
    {
        $form = new UpdatePasswordForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }

    // TODO 即将废弃
    public function actionCurrentMchSetting()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getMchSetting());
    }
}
