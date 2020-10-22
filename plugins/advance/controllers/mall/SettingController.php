<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 9:44
 */

namespace app\plugins\advance\controllers\mall;

use app\plugins\advance\forms\common\BannerListForm;
use app\plugins\advance\forms\mall\AdvanceSettingForm;
use app\plugins\advance\forms\mall\BannerForm;
use app\plugins\advance\models\TemplateForm;
use app\plugins\Controller;

class SettingController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AdvanceSettingForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new AdvanceSettingForm();
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

    public function actionBanner()
    {
        return $this->render('banner');
    }

    public function actionBannerStore()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new BannerListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new BannerForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }
}