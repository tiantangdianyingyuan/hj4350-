<?php


namespace app\controllers\mall;


use app\forms\mall\statistics\PriceForm;

class PriceStatisticsController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PriceForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->get('flag') === 'EXPORT') {
                $form = new PriceForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->search());
            } else {
                return $this->render('index');
            }
        }
    }
}
