<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\controllers\mall;

use app\plugins\Controller;
use app\plugins\gift\forms\mall\GiftDeleteForm;
use app\plugins\gift\forms\mall\GiftListForm;
use app\plugins\gift\forms\mall\GiftRefundForm;

class RecordController extends Controller
{
    public function actionTribute()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getSendList());
        } else {
            return $this->render('tribute');
        }
    }

    public function actionTributeDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getRecordList());
        } else {
            return $this->render('tribute-detail');
        }
    }

    public function actionReceive()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getRecordList());
        } else {
            return $this->render('receive');
        }
    }

    public function actionRefund()
    {
        $form = new GiftRefundForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->refundGift());
    }

    public function actionDel()
    {
        $form = new GiftDeleteForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->del());
    }
}
