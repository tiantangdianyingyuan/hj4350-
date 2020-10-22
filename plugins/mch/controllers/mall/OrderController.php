<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\mall;

use app\forms\mall\order\OrderRefundListForm;
use app\forms\mall\order\OrderSendForm;
use app\plugins\Controller;
use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\plugins\mch\forms\mall\MchOrderForm;
use app\plugins\mch\forms\mall\OrderForm;

class OrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $form->is_mch = 1;
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new MchOrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->is_mch = 1;
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('detail');
        }
    }

    //清空回收站
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->destroyAll());
        }
    }

    //售后订单列表
    public function actionRefund()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
            } else {
                return $this->render('refund');
            }
        }
    }

    //批量发货
    public function actionBatchSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendForm();
            if (\Yii::$app->request->isPost) {
                $form->is_express = 1;
                $form->attributes = \Yii::$app->request->post();
                return $form->batchSave();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->batchDetail());
            }
        } else {
            return $this->render('batch-send');
        }
    }
}
