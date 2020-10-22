<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\controllers\mall;

use app\plugins\Controller;
use app\plugins\lottery\forms\mall\BannerForm;

class BannerController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BannerForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BannerForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }
}
