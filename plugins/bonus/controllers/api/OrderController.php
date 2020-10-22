<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/6
 * Email: <657268722@qq.com>
 */

namespace app\plugins\bonus\controllers\api;


use app\plugins\bonus\forms\mall\BonusForm;
use app\plugins\bonus\forms\mall\OrderForm;

class OrderController extends ApiController
{
    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->captain_id = \Yii::$app->user->id;
        return $this->asJson($form->search());
    }

    /**
     * 团员列表
     * @return \yii\web\Response
     */
    public function actionTeamBonus()
    {
        $form = new BonusForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        $form->captain_id = \Yii::$app->user->id;
        return $this->asJson($form->teamBonus());
    }

    /**
     * 统计图表
     * @return \yii\web\Response
     */
    public function actionData()
    {
        $form = new BonusForm();
        $form->captain_id = \Yii::$app->user->id;
        return $this->asJson($form->historicalData());
    }
}