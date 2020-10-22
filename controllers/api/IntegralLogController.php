<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\IntegralLogForm;

class IntegralLogController extends ApiController
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
        $form = new IntegralLogForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }
}
