<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\controllers\api;


use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderPriceForm;
use app\forms\common\order\OrderRefundForm;
use app\forms\common\order\OrderSendForm;
use app\forms\common\order\PrintForm;
use app\forms\mall\order\OrderUpdateAddressForm;
use app\models\MallSetting;
use app\plugins\mch\Plugin;
use app\plugins\mch\controllers\api\filter\MchLoginFilter;
use app\plugins\mch\forms\api\OrderDetailForm;
use app\plugins\mch\forms\api\OrderForm;
use app\plugins\mch\forms\api\OrderSubmitForm;
use app\plugins\mch\forms\api\RefundOrderDetailForm;
use app\plugins\mch\forms\api\RefundOrderForm;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'mchLogin' => [
                'class' => MchLoginFilter::class,
                'ignore' => [
                    'plugin/mch/api/order/preview',
                    'plugin/mch/api/order/submit',
                ]
            ]
        ]);
    }

    /**
     * 订单列表
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getList());
    }

    /**
     * 订单详情
     * @return \yii\web\Response
     */
    public function actionDetail()
    {
        $form = new OrderDetailForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getDetail());
    }

    /**
     * 售后订单详情
     * @return \yii\web\Response
     */
    public function actionRefundDetail()
    {
        $form = new RefundOrderDetailForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->getOrderRefundDetail());
    }

    /**
     * 订单发货
     * @return \yii\web\Response
     */
    public function actionOrderSend()
    {
        $form = new OrderSendForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->save());
    }

    //获取面单
    public function actionPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    // 修改商品单价
    public function actionUpdatePrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updatePrice());
        }
    }

    // 修改订单总价+运费
    public function actionUpdateTotalPrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTotalPrice());
        }
    }

    // 处理售后订单
    public function actionRefundHandle()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    //订单取消
    public function actionCancel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderCancelForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setEnableData()->preview());
    }

    public function actionSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setEnableData()->submit());
    }

    // 更新订单地址
    public function actionUpdateAddress()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderUpdateAddressForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }
}
