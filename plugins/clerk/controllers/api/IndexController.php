<?php

namespace app\plugins\clerk\controllers\api;


use app\forms\api\card\UserCardForm;
use app\plugins\clerk\forms\OrderDetailForm;
use app\plugins\clerk\forms\OrderForm;
use app\models\ClerkUser;
use app\plugins\clerk\forms\StatisticsForm;
use app\plugins\clerk\forms\UserClerkForm;

class IndexController extends ApiController
{
    //核销订单
    public function actionOrder()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->app_clerk = 1;
        $form->is_offline = 1;
        return $this->asJson($form->search());
    }

    //我的核销订单
    public function actionMyOrder()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $clerk_ids = ClerkUser::find()->select('id')->andWhere(['user_id' => \Yii::$app->user->id])->asArray()->all();
        $arr = [];
        foreach ($clerk_ids as $clerk_id) {
            $arr[] = $clerk_id['id'];
        }
        $form->clerk_id = $arr;
        $form->is_offline = 1;
        return $this->asJson($form->search());
    }

    //订单详情
    public function actionDetail()
    {
        $form = new OrderDetailForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    //卡卷核销列表
    public function actionCard()
    {
        $form = new UserCardForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getList());
    }

    //我的卡卷核销列表
    public function actionMyCard()
    {
        $form = new UserCardForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->clerk_id = \Yii::$app->user->id;
        return $this->asJson($form->getList());
    }

    //核销统计
    public function actionStatistics()
    {
        $form = new StatisticsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    //核销员信息
    public function actionClerkInfo()
    {
        $form = new UserClerkForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->clerk_id = \Yii::$app->user->id;
        return $this->asJson($form->search());
    }
}
