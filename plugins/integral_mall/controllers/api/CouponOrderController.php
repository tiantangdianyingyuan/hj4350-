<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\controllers\api\ApiController;
use app\plugins\integral_mall\forms\api\coupon\CouponOrderForm;
use app\plugins\integral_mall\forms\api\coupon\CouponOrderPayForm;
use app\plugins\integral_mall\forms\api\coupon\CouponOrderSubmitForm;

class CouponOrderController extends ApiController
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
        $form = new CouponOrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new CouponOrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->detail());
    }

    public function actionOrderSubmit()
    {
        $form = new CouponOrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->submit());
    }

    public function actionOrderPayData()
    {
        $form = new CouponOrderPayForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getResponseData());
    }
}
