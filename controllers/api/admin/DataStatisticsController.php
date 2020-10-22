<?php


namespace app\controllers\api\admin;


use app\forms\mall\statistics\DataForm;

class DataStatisticsController extends AdminController
{
    //小程序管理页面首页总览数据
    public function actionAll_data()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->all_search());
    }

    //图表查询
    public function actionTable()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->table_search());
    }
}
