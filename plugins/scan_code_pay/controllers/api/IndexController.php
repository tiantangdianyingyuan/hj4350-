<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\scan_code_pay\forms\api\IndexForm;
use app\plugins\scan_code_pay\forms\api\QrCodeForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new IndexForm();
        return $this->asJson($form->search());
    }

    public function actionQrCode()
    {
        $form = new QrCodeForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getQrCode());
    }
}