<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\behaviors\LoginFilter;
use app\plugins\diy\forms\api\AdUserCouponForm;


class AdAwardController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionReceive()
    {
        $form = new AdUserCouponForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->receive());
    }
}