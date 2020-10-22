<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\controllers\mall;


use app\plugins\Controller;
use app\plugins\diy\forms\mall\TemplateForm;


class HomeController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateForm();
            return $this->asJson($form->getHome());
        } else {
            return $this->render('index');
        }
    }
}