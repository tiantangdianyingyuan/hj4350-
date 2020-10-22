<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\controllers\mall;

use app\plugins\booking\forms\mall\GoodsListForm;
use app\plugins\Controller;
use app\plugins\booking\forms\mall\GoodsEditForm;
use app\plugins\booking\forms\mall\GoodsForm;

class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->get('search');
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();
                $form = new GoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attributes = json_decode($data['form'], true)['detail'];
                $form->attrGroups = json_decode($data['attrGroups'], true);
                return $this->asJson($form->save());
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionStoreSearch()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->storeSearch());
        }
    }
}
