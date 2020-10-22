<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/26
 * Time: 10:55
 */

namespace app\controllers\mall;

use app\controllers\mall\MallController;
use app\forms\mall\city_service\CityServiceEditForm;
use app\forms\mall\city_service\CityServiceForm;

class CityServiceController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CityServiceForm();
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
                $form = new CityServiceEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new CityServiceForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionOption()
    {
        $form = new CityServiceForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getOption());
    }

    public function actionDelete()
    {
        $form = new CityServiceForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}
