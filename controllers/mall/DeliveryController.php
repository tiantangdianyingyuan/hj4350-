<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019年8月20日 09:17:13
 */


namespace app\controllers\mall;


use app\forms\mall\delivery\DeliveryForm;
use app\forms\mall\delivery\ManForm;

class DeliveryController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DeliveryForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getData());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        $form = new DeliveryForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->edit());
    }

    public function actionMobile()
    {
        $form = new DeliveryForm();
        return $this->asJson($form->mobile());
    }

    public function actionMan()
    {
        $form = new ManForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionManList()
    {
        $form = new ManForm();
        return $this->asJson($form->search());
    }

    public function actionManDelete()
    {
        $form = new ManForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->destroy());
    }
}
