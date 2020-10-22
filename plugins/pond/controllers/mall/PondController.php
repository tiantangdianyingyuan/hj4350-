<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\controllers\mall;

use app\core\response\ApiCode;
use app\plugins\Controller;
use app\plugins\pond\forms\mall\PondForm;

class PondController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PondForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionSearch()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PondForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
    }
}
