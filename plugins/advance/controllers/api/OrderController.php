<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\advance\forms\api\AdvanceForm;
use app\plugins\advance\forms\api\AdvanceOrderForm;
use app\plugins\advance\forms\api\AdvanceOrderPayForm;
use app\plugins\advance\forms\api\OrderForm;
use app\plugins\advance\forms\api\OrderSubmitForm;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->detail());
    }

    public function actionOrderPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->submit());
    }

    //下定金
    public function actionAdvance()
    {
        $form = new AdvanceOrderForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->advance());
    }

    //我的定金订单
    public function actionMyAdvance()
    {
        $form = new AdvanceForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    //我的定金订单详情
    public function actionMyDetail()
    {
        $form = new AdvanceForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->detail());
    }

    //定金付款
    public function  actionPayData(){
        $form=new AdvanceOrderPayForm();
        $form->attributes=\Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }
}
