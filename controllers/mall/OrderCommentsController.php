<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\mall;

use app\forms\mall\order_comments\OrderCommentsForm;
use app\forms\mall\order_comments\OrderCommentsEditForm;
use app\forms\mall\order_comments\OrderCommentsReplyForm;

class OrderCommentsController extends MallController
{
    public $enableCsrfValidation = false;
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderCommentsEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new OrderCommentsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionReply()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderCommentsReplyForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form = new OrderCommentsReplyForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('reply');
        }
    }

    public function actionGoodsSearch()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->goodsSearch());
        }
    }

    public function actionDestroy()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentsForm();
            $form->id = $id;
            return $this->asJson($form->destroy());
        }
    }

    public function actionShow()
    {
        if ($id = \Yii::$app->request->post('id')) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->show());
        }
    }

    public function actionBatchReply()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchReply());
        }
    }

    public function actionBatchDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchDestroy());
        }
    }

    public function actionBatchUpdateStatus()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchUpdateStatus());
        }
    }

    public function actionUpdateTop() {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderCommentsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTop());
        }
    }
}
