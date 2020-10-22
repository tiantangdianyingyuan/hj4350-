<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 11:15
 */

namespace app\plugins\vip_card\controllers\mall;

use app\plugins\Controller;
use app\plugins\vip_card\forms\mall\UserEditForm;
use app\plugins\vip_card\forms\mall\UserForm;

class UserController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            }
        }
        return $this->render('index');
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UserEditForm();
                $form->attributes = \Yii::$app->request->post();
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new UserForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionSwitchExpire()
    {
        if (\Yii::$app->request->isPost) {
            $form = new UserEditForm();
            $form->scenario = 'expire';
            $form->attributes = \Yii::$app->request->post();
            $res = $form->switchExpire();
            return $this->asJson($res);
        }
    }

    public function actionDelete()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    public function actionSearch()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    public function actionRight()
    {
        $form = new UserForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->right());
    }
}