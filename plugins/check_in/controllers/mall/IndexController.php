<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 13:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\controllers\mall;


use app\plugins\check_in\forms\mall\CheckLogForm;
use app\plugins\check_in\forms\mall\ConfigEditForm;
use app\plugins\check_in\forms\mall\ConfigForm;
use app\plugins\check_in\forms\mall\CustomizeForm;
use app\plugins\check_in\forms\mall\TemplateForm;

class IndexController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ConfigForm();
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->search());
            }
            if (\Yii::$app->request->isPost) {
                $form = new ConfigEditForm();
                $post = \Yii::$app->request->post();
                $list = \Yii::$app->serializer->decode($post['form']);
                $form->attributes = (array)$list;
                $form->mall = \Yii::$app->mall;
                return $this->asJson($form->save());
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
                return $this->asJson($form->getDetail($add, $platform));
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

    public function actionCustomize()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
            if (\Yii::$app->request->isPost) {
                $form = new CustomizeForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
        return $this->render('customize');
    }

    public function actionLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CheckLogForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('log');
    }
}

