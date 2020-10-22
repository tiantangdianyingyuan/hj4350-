<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\controllers\mall;


use app\plugins\Controller;
use app\plugins\diy\forms\mall\ModuleEditForm;
use app\plugins\diy\forms\mall\ModuleForm;
use app\plugins\diy\forms\mall\TemplateForm;

class ModuleController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ModuleForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
        return $this->render('index');
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ModuleEditForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->post());
            } else {
                $form = new ModuleForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->get());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        $form = new ModuleForm();
        $id = \Yii::$app->request->get('id');
        return $this->asJson($form->destroy($id));
    }

    public function actionMarketSearch()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();
        $form->templateType = 'module';
        return $this->asJson($form->getMarketList());
    }
}
