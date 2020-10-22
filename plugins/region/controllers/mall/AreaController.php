<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/3/13
 * Time: 14:50
 */

namespace app\plugins\region\controllers\mall;

use app\plugins\Controller;
use app\plugins\region\forms\mall\AreaEditForm;
use app\plugins\region\forms\mall\AreaForm;

class AreaController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isGet) {
            $form = new AreaForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AreaForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDelete()
    {
        if (\Yii::$app->request->isPost) {
            $form = new AreaForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->delete());
        }
    }
}
