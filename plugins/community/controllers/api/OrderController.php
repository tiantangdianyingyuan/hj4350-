<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 17:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\controllers\api;


use app\plugins\community\forms\api\OrderForm;
use app\plugins\community\forms\api\OrderListForm;
use app\plugins\community\forms\api\OrderSubmitForm;

class OrderController extends ApiController
{
    public function actionList()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->type = 1;
        return $this->asJson($form->getList());
    }

    public function actionDetail()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->post();
        $form->type = 1;
        return $this->asJson($form->detail());
    }

    //团长订单
    public function actionMiddleList()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->type = 2;
        return $this->asJson($form->getList());
    }

    public function actionMiddleDetail()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->post();
        $form->type = 2;
        return $this->asJson($form->detail());
    }

    public function actionOrderPreview()
    {
        $form = new OrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new OrderSubmitForm();
        $form->attributes = \Yii::$app->request->post();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->submit());
    }

    /**
     * 订单确认收货
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->orderConfirm());
    }
}
