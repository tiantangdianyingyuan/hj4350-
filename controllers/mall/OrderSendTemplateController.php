<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\mall\order_send_template\AddressEditForm;
use app\forms\mall\order_send_template\AddressForm;
use app\forms\mall\order_send_template\OrderForm;
use app\forms\mall\order_send_template\OrderSendTemplateEditForm;
use app\forms\mall\order_send_template\OrderSendTemplateForm;
use app\forms\mall\order_send_template\UploadForm;

class OrderSendTemplateController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendTemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->main($form::getList));
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new OrderSendTemplateEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());

            } else {
                $form = new OrderSendTemplateForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->main($form::getDetail));
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDefaultTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendTemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->main($form::getDefaultTemplate));
        }
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendTemplateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->main($form::destroy));
        }
    }

    public function actionOrder()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
    }

    /**
     * 网点信息
     */
    public function actionAddress()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AddressForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->address());
        }
    }

    /**
     * 网点添加|编辑
     * @return string|\yii\web\Response
     */
    public function actionAddressEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AddressEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionUploadImage()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UploadForm();
                $form->img_list = \Yii::$app->request->post('img_list');
                return $this->asJson($form->save());
            }
        }
    }
}