<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;


use app\forms\mall\template_msg\TemplateEditForm;
use app\forms\mall\template_msg\TemplateForm;
use app\forms\mall\template_msg\TemplateSendForm;
use app\forms\mall\template_msg\TestUserForm;
use app\forms\mall\template_msg\WxPlatformEditForm;
use app\forms\mall\template_msg\WxPlatformMsgForm;

class TemplateMsgController extends MallController
{
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new WxPlatformMsgForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new WxPlatformEditForm();
                $data = \Yii::$app->request->post('form');
                $form->attributes = $data;
                $form->template_list = $data['template_list'];
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionMpTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new WxPlatformMsgForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->mpTemplate());
        }
    }

    public function actionTest()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new WxPlatformMsgForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->test());
        }
    }

    /**
     * 获取用户
     */
    public function actionUsers()
    {
        $form = new TemplateForm();
        $form->attributes = \Yii::$app->request->get();

        if (\Yii::$app->request->get('is_all')) {
            return $form->getAllUsers();
        } else {
            $platform = \Yii::$app->request->get('platform');
            return $this->asJson($form->getUsers($platform));
        }
    }

    /**
     * 单个模板消息发送测试
     */
    public function actionTestSend()
    {
        $form = new TestUserForm();
        $form->user_id = \Yii::$app->request->post('user_id');
        $form->tpl_id = \Yii::$app->request->post('tpl_id');
        return $this->asJson($form->testSend());
    }

    public function actionAddTestUser()
    {
        $form = new TestUserForm();
        $form->user = \Yii::$app->request->post('user');

        return $this->asJson($form->addTestUser());
    }

    public function actionTestUser()
    {
        $form = new TestUserForm();
        return $this->asJson($form->getTestUser());
    }

    public function actionQrcode()
    {
        $form = new TestUserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getQrcode());
    }
}
