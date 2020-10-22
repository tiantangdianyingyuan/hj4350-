<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\controllers\api;

use app\controllers\api\ApiController;
use app\plugins\integral_mall\forms\api\coupon\CouponForm;
use app\plugins\integral_mall\forms\common\CouponListForm;

class CouponController extends ApiController
{
    public function actionIndex()
    {
        $form = new CouponListForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->search());
    }

    public function actionDetail()
    {
        $form = new CouponForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->detail());
    }
}
