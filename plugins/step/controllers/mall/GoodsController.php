<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\controllers\mall;

use app\plugins\Controller;
use app\plugins\step\forms\mall\GoodsEditForm;
use app\plugins\step\forms\mall\GoodsForm;
use app\plugins\step\forms\mall\GoodsListForm;

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
                $form = new GoodsEditForm();
                $data = \Yii::$app->request->post();
                $form->attributes = json_decode($data['form'], true);
                $form->attributes = json_decode($data['form'], true)['detail'];
                $form->attrGroups = json_decode($data['attrGroups'], true);
                return $this->asJson($form->save());
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    // TODO 不再使用 即将废弃
    public function actionEditSort()
    {
        if(\Yii::$app->request->isPost) {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->editSort());
        }
    }
}
