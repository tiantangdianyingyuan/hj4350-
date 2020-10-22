<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 11:15
 */

namespace app\plugins\vip_card\controllers\mall;

use app\plugins\Controller;
use app\plugins\vip_card\forms\mall\CardDetailEditForm;
use app\plugins\vip_card\forms\mall\CardDetailForm;
use app\plugins\vip_card\forms\mall\CardEditForm;
use app\plugins\vip_card\forms\mall\CardForm;
use app\plugins\vip_card\forms\mall\CatsForm;

class CardController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CardForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 添加、编辑主卡
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CardEditForm();
                $form->attributes = \Yii::$app->request->post();
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new CardForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionEditDetail()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CardDetailEditForm();
                $form->attributes = \Yii::$app->request->post();
                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new CardDetailForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionEditSort()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editSort();

        return $this->asJson($res);
    }

    public function actionSwitchStatus()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->switchStatus());
    }

    public function actionDestroy()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->destroy());
    }

    public function actionSwitchDetailStatus()
    {
        $form = new CardDetailForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->switchStatus());
    }

    public function actionDetailDestroy()
    {
        $form = new CardDetailForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->destroy());
    }

    public function actionCoupons()
    {
        $form = new CardDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCoupons());
    }

    public function actionCards()
    {
        $form = new CardDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getCards());
    }

    public function actionCats()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CatsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        }
    }

    public function actionRight()
    {
        $form = new CardForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->right());
    }
}