<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\mall;


use app\plugins\Controller;
use app\plugins\mch\forms\mall\CommonCatEditForm;
use app\plugins\mch\forms\mall\CommonCatForm;

class CommonCatController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CommonCatForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CommonCatEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new CommonCatForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionSwitchStatus()
    {
        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->switchStatus());
    }

    public function actionDestroy()
    {
        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->destroy());
    }

    public function actionAllList()
    {
        $form = new CommonCatForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->allList());
    }
}
