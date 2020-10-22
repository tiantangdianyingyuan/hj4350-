<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\recharge\RechargeForm;
use app\forms\api\recharge\RechargeOrderForm;
use app\forms\api\recharge\RechargeSettingForm;

class RechargeController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'only' => ['balance-recharge'],
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new RechargeForm();
        $res = $form->getIndex();

        return $res;
    }

    public function actionBalanceRecharge()
    {
        $form = new RechargeOrderForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->balanceRecharge();

        return $res;
    }

    public function actionSetting()
    {
        $form = new RechargeSettingForm();
        $res = $form->getIndex();

        return $res;
    }
}
