<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;

use app\forms\common\CommonQrCode;
use app\forms\mall\live\GoodsEditForm;
use app\forms\mall\live\GoodsForm;
use app\forms\mall\live\LiveAddGoods;
use app\forms\mall\live\LiveEditForm;
use app\forms\mall\live\LiveForm;

class LiveController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new LiveForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionGoods()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('goods');
        }
    }

    public function actionGoodsEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new GoodsEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {

            }
        } else {
            return $this->render('goods-edit');
        }
    }

    public function actionDeleteGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->deleteGoods());
    }

    public function actionSubmitAudit()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->submitAudit());
    }

    public function actionCancelAudit()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->cancelAudit());
    }

    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionLiveEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LiveEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {

            }
        } else {
            return $this->render('live-edit');
        }
    }

    public function actionAddGoods()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LiveAddGoods();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {

            }
        } else {
            return $this->render('add-goods');
        }
    }

    public function actionQrCode()
    {
        $form = new LiveForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getQrCode());
    }
}
