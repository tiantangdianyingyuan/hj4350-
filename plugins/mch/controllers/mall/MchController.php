<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\mall;


use app\plugins\Controller;
use app\plugins\mch\forms\mall\MchEditForm;
use app\plugins\mch\forms\mall\MchForm;
use app\plugins\mch\forms\mall\MchMallSettingEditForm;
use app\plugins\mch\forms\mall\MchMallSettingForm;
use app\plugins\mch\forms\mall\MchReviewForm;

class MchController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new MchForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchEditForm();
                $data = \Yii::$app->request->post('form');
                $form->attributes = $data;
                $form->province_id = $data['district'][0];
                $form->city_id = $data['district'][1];
                $form->district_id = $data['district'][2];
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new MchForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->destroy());
    }

    public function actionSwitchStatus()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->switchStatus());
    }

    public function actionMallSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchMallSettingEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new MchMallSettingForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('mall-setting');
        }
    }

    public function actionReview()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new MchReviewForm();
                $form->attributes = \Yii::$app->request->get();

                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('review');
        }
    }

    /**
     * 员工登录入口链接
     * @return \yii\web\Response
     */
    public function actionRoute()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->route();

        return $this->asJson($res);
    }

    public function actionUpdatePassword()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->post('form');
        $res = $form->updatePassword();

        return $this->asJson($res);
    }

    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new MchForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->searchUser());
        }
    }

    public function actionEditSort()
    {
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->editSort());
    }
}
