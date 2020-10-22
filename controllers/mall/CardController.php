<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\mall;


use app\forms\mall\card\CardEditForm;
use app\forms\mall\card\CardForm;
use app\forms\mall\card\CardSwitchForm;

class CardController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new CardForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->getList();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CardEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new CardForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }


    /**
     * 获取商品卡券列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new CardForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    public function actionHistoryList()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getHistoryList();

        return $this->asJson($res);
    }

    public function actionSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CardForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->send();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('send');
        }
    }

    public function actionSwitchSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CardSwitchForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }
}
