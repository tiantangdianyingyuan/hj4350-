<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/4
 * Time: 10:32
 */


namespace app\plugins\gift\controllers\mall;

use app\plugins\gift\forms\mall\GiftGoodsCatsForm;
use app\plugins\gift\forms\mall\GoodsForm;
use app\plugins\Controller;
use app\plugins\gift\forms\mall\GoodsEditForm;


class GoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->getList();

                return $this->asJson($res);
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();
                $form = new GoodsEditForm();
                $form->attributes = json_decode($data['form'], true);
                $form->attrGroups = json_decode($data['attrGroups'], true);
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionCategories()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GiftGoodsCatsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('categories');
        }
    }

    //添加分类
    public function actionAddCarts()
    {
        $form = new GiftGoodsCatsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->add());
    }

    //分类排序
    public function actionSortCarts()
    {
        $form = new GiftGoodsCatsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->sort());
    }

    //删除分类
    public function actionDelCarts()
    {
        $form = new GiftGoodsCatsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->del());
    }

}