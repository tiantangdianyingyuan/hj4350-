<?php
/**
 * link=> 域名
 * copyright: Copyright (c) 2018 人人禾匠商城
 * author: wxf
 */

namespace app\plugins\copy\controllers\mall;


use app\plugins\Controller;
use app\plugins\copy\forms\mall\CatListForm;
use app\plugins\copy\forms\mall\CopyForm;
use app\plugins\copy\forms\mall\GoodsAddForm;
use app\plugins\copy\forms\mall\GoodsListForm;
use app\plugins\copy\forms\mall\StoreEditForm;
use app\plugins\copy\forms\mall\StoreForm;
use app\plugins\copy\forms\mall\TemplateEditForm;

class IndexController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }



    public function actionHome(){
        return $this->render('home');
    }


    /**
     * 首页商品
     * @return \yii\web\Response
     */
    public function actionHomeTemplate()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateEditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getPage());
        }
    }


    public function actionCopyTemplate(){
        if (\Yii::$app->request->isAjax) {
            $form = new TemplateEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    /**
     * 门店列表
     * @return string|\yii\web\Response
     */
    public function actionStoreList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加门店
     * @return string|\yii\web\Response
     */
    public function actionStoreAdd()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreEditForm();
            $form->attributes = \Yii::$app->request->post("form");
            return $this->asJson($form->save());
        }
    }

    /**
     * 删除门店
     * @return string|\yii\web\Response
     */
    public function actionStoreDel($id)
    {
        if (\Yii::$app->request->isAjax) {
            $form = new StoreEditForm();
            return $this->asJson($form->del($id));
        } else {


            return $this->render('index');
        }
    }


    /**
     * 商品分类
     */
    public function actionCatList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CatListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }


    /**
     * 商品
     * @return \yii\web\Response
     */
    public function actionGoodsList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }

    /**
     * 首页商品
     * @return \yii\web\Response
     */
    public function actionHomeGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getHomeGoods());
        }
    }



    /**
     * 获取自己门店分类
     * @return \yii\web\Response
     */
    public function actionStoreCat()
    {
        $form = new CopyForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCatAllList());
    }


    public function actionCopyGoods()
    {
        $form = new GoodsAddForm();
        $data = \Yii::$app->request->post();
        $form->attributes = $data;
        return $this->asJson($form->copy());
    }


}
