<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\mall\mch\CashEditForm;
use app\forms\mall\mch\FinancialManageForm;
use app\forms\mall\mch\MchEditForm;
use app\forms\mall\mch\MchSettingEditForm;
use app\forms\mall\mch\MchSettingForm;

class MchController extends MallController
{
    public function actionManage()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchEditForm();
                $form->attributes = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
            }
        } else {
            return $this->render('manage');
        }
    }

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchSettingEditForm();
                $form->attributes = \Yii::$app->request->post();

                return $this->asJson($form->save());
            } else {
                $form = new MchSettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionAccountLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAccountLog());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getAccountLog();
                return false;
            } else {
                return $this->render('account-log');
            }
        }
    }

    public function actionCashLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getCashLog());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getCashLog();
                return false;
            } else {
                return $this->render('cash-log');
            }
        }
    }

    public function actionOrderCloseLog()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getOrderCloseLog());
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new FinancialManageForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getOrderCloseLog();
                return false;
            } else {
                return $this->render('order-close-log');
            }
        }
    }

    public function actionCashSubmit()
    {
        $form = new CashEditForm();
        $data = \Yii::$app->request->post('form');
        $form->attributes = $data;
        $form->mch_id = \Yii::$app->user->identity->mch_id;
        $form->type_data = \Yii::$app->serializer->encode($data['type_data']);

        return $this->asJson($form->save());
    }

    public function actionMchMallSetting()
    {
        $form = new MchSettingForm();
        return $this->asJson($form->getMchMallSetting());
    }

    public function actionMchSetting()
    {
        $form = new MchSettingForm();
        return $this->asJson($form->getMchSetting());
    }
}
