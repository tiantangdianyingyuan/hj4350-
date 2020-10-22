<?php


namespace app\controllers\mall;


use app\forms\common\notice\NoticeForm;
use app\forms\mall\statistics\DataForm;
use app\forms\mall\statistics\InitDataForm;

class DataStatisticsController extends MallController
{
    //页面渲染，top表
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionNotice()
    {
        $from = new NoticeForm();
        $from->attributes = \Yii::$app->request->post();
        $from->type = 3;
        return $this->asJson($from->getList());
    }

    //头部总数据
    public function actionAllData()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->head_all());
    }

    //头部总数据
    public function actionAllNum()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $form->data_search();
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->data_search());
        }
    }

    //图表查询
    public function actionTable()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->table_search());
    }

    //插件菜单
    public function actionPluginMenus()
    {
        $form = new DataForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->menus());
    }

    //商品查询-排序
    public function actionGoods_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(1);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(1));
        }
    }

    //用户查询-排序
    public function actionUsers_top()
    {
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->post();
            $form->search(2);
            return false;
        } else {
            $form = new DataForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search(2));
        }
    }

    // 数据初始
    public function actionInitial()
    {
        $form = new InitDataForm();
        return $this->asJson($form->search());
    }
}
