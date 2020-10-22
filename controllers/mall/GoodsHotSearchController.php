<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\controllers\mall;

use app\forms\mall\goods\hot\SearchAll;
use app\forms\mall\goods\hot\SearchDestroy;
use app\forms\mall\goods\hot\SearchEdit;

class GoodsHotSearchController extends MallController
{
    public function actionGetAll()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SearchAll();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->select());
        }
        return $this->render('@app/views/mall/goods/hot-search');
    }

    public function actionChangeSort()
    {
        if (\Yii::$app->request->isPost) {
            $form = new SearchAll();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeSort());
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SearchEdit();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->detail());
            }
        }
        return $this->render('@app/views/mall/goods/hot-search-edit');
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new SearchDestroy();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destory());
        }
    }
}
