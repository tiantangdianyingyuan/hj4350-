<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\controllers\mall;

use app\plugins\Controller;
use app\plugins\shopping\forms\mall\LikeGoodsEditForm;
use app\plugins\shopping\forms\mall\LikeGoodsForm;
use app\plugins\shopping\forms\mall\LikeGoodsUserEditForm;
use app\plugins\shopping\forms\mall\LikeGoodsUsersForm;

class LikeGoodsController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsForm();
            $form->attributes = \Yii::$app->request->get();
            $form->keyword = \Yii::$app->serializer->decode(\Yii::$app->request->get('search'))['keyword'];
            return $this->asJson($form->getAddList());
        } else {
            return $this->render('index');
        }
    }

    //订单详情
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new LikeGoodsEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->add());
            } else {
                $form = new LikeGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $form->keyword = \Yii::$app->serializer->decode(\Yii::$app->request->get('search'))['keyword'];
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }

    public function actionUsers()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsUsersForm();
            $form->attributes = \Yii::$app->request->get();
            $form->keyword = \Yii::$app->serializer->decode(\Yii::$app->request->get('search'))['keyword'];
            return $this->asJson($form->getList());
        } else {
            return $this->render('users');
        }
    }

    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUsers());
        } else {
            return $this->render('users');
        }
    }

    public function actionAddLikeUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->add());
        } else {
            return $this->render('users');
        }
    }

    public function actionDestroyLikeUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LikeGoodsUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroyLikeUser());
        } else {
            return $this->render('users');
        }
    }
}
