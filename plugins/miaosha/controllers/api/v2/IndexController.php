<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\controllers\api\v2;

use app\controllers\api\ApiController;
use app\controllers\api\filters\LoginFilter;
use app\plugins\miaosha\forms\api\v2\IndexForm;
use app\plugins\miaosha\forms\api\v2\MsPosterForm;

class IndexController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['index']
            ],
        ]);
    }

    public function actionAddCart()
    {
        $form = new IndexForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->addCart());
    }

    public function actionPoster()
    {
        $form = new MsPosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
