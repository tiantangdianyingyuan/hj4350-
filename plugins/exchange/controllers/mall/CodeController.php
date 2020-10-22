<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\controllers\mall;

use app\plugins\Controller;
use app\plugins\exchange\forms\mall\CodeEditForm;
use app\plugins\exchange\forms\mall\CodeForm;

class CodeController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    //v
    public function actionList()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CodeForm();
            $form->attributes = \Yii::$app->request->post();
            $form->flag = 'EXPORT';
            return $this->asJson($form->getList());
        }
        if (\Yii::$app->request->isAjax) {
            $form = new CodeForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
        return $this->render('list');
    }
    //v
    public function actionAppend()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CodeEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->append());
        }
    }

    //v
    public function actionBan()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CodeEditForm();
            $form->id = \Yii::$app->request->post('id');
            return $this->asJson($form->ban());
        }
    }
}
