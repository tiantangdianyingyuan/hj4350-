<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\mall;


use app\plugins\Controller;
use app\plugins\mch\forms\mall\SettingEditForm;
use app\plugins\mch\forms\mall\SettingForm;
use app\plugins\mch\forms\mall\TemplateForm;

class SettingController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SettingEditForm();
                $form->form = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new SettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new TemplateForm();
                $form->mall = \Yii::$app->mall;
                $add = \Yii::$app->request->get('add');
                $platform = \Yii::$app->request->get('platform');
                return $this->asJson($form->getDetail($add,$platform));
            }
            if (\Yii::$app->request->isPost) {
                $form = new TemplateForm();
                $form->attributes = \Yii::$app->request->post();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
            }
        }
        return $this->render('template');
    }
}
