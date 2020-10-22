<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\controllers\mall;

use app\plugins\Controller;
use app\plugins\exchange\forms\mall\CardOrderForm;
use app\plugins\exchange\forms\mall\goods\CardGoodsEditForm;
use app\plugins\exchange\forms\mall\goods\CardGoodsForm;
use app\plugins\exchange\forms\mall\goods\CardGoodsListForm;
use app\plugins\exchange\forms\mall\LibraryForm;

class CardGoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new CardGoodsListForm();
            $chooseList = \Yii::$app->request->post('choose_list');
            $form->choose_list = $chooseList ? explode(',', $chooseList) : [];
            $form->flag = 'EXPORT';
            return $this->asJson($form->getList());
        }
        if (\Yii::$app->request->isAjax) {
            $form = new CardGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->get('search');
            $res = $form->getList();
            return $this->asJson($res);
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();
                $form = new CardGoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attrGroups = json_decode($data['attrGroups'], true);
                return $this->asJson($form->save());
            } else {
                $form = new CardGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();
                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionOrderLog()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CardOrderForm();
            $form->attributes = \Yii::$app->request->post();
            $form->flag = 'EXPORT';
            return $this->asJson($form->search());
        }

        if (\Yii::$app->request->isAjax) {
            $form = new CardOrderForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('order-log');
        }
    }
    //
    public function actionLibraryAll()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LibraryForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getAll());
        }
    }
}
