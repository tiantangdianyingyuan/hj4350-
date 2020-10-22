<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\mall;

use app\core\CsvExport;
use app\forms\common\order\OrderCancelForm;
use app\forms\common\order\OrderPriceForm;
use app\forms\common\order\OrderRefundForm;
use app\forms\common\order\PrintForm;
use app\forms\mall\order\OrderClerkForm;
use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderPrintForm;
use app\forms\mall\order\OrderRefundListForm;
use app\forms\mall\order\OrderSendForm;
use app\forms\mall\order\OrderUpdateAddressForm;

class OrderController extends MallController
{
    public function init()
    {
        \Yii::$app->validateCloudFile();

        /* 请勿删除下面代码↓↓￿↓↓￿ */
        if (method_exists(\Yii::$app, '。')) {
            $pass = \Yii::$app->。();
        } else {
            if (function_exists('usleep')) usleep(rand(100, 1000));
            $pass = false;
        }
        if (!$pass) {
            if (function_exists('sleep')) sleep(rand(30, 60));
            header('HTTP/1.1 504 Gateway Time-out');
            exit;
        }
        /* 请勿删除上面代码↑↑↑↑ */

        return parent::init();
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->search();
            return $this->asJson($res);
        } else {
            return $this->render('detail');
        }
    }


    //添加备注
    public function actionSellerRemark()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->sellerRemark();
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

    //订单删除
    public function actionDestroy()
    {
        if ($order_id = \Yii::$app->request->post('order_id')) {
            $form = new OrderDestroyForm();
            $form->order_id = $order_id;
            return $this->asJson($form->destroy());
        }
    }

    //回收站
    public function actionRecycle()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            //$form->order_id = 236;
            //$form->is_recycle = 1; 1回收 0恢复
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->recycle());
        }
    }

    //清空回收站
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->destroyAll());
        }
    }

    //订单发货
    public function actionSend()
    {
        if (\Yii::$app->request->isPost) {
            $form = new \app\forms\common\order\OrderSendForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    //批量发货
    public function actionBatchSend()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderSendForm();
            if (\Yii::$app->request->isPost) {
                $form->is_express = 1;
                $form->attributes = \Yii::$app->request->post();
                return $form->batchSave();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->batchDetail());
            }
        } else {
            return $this->render('batch-send');
        }
    }

    // 默认模板下载
    public function actionBatchSendModel()
    {
        $csv = new CsvExport();
        $fileName = date('YmdHis', time()).'.xlsx';
        return $csv->downloadBatchSend($fileName);
    }

    // 修改运费
    public function actionUpdateExpress()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateExpress());
        }
    }

    // 修改价格
    public function actionUpdatePrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updatePrice());
        }
    }

    // 修改总价格
    public function actionUpdateTotalPrice()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderPriceForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->updateTotalPrice());
        }
    }

    //货到付款，确认收货
    public function actionConfirm()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->confirm());
        }
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

    //获取面单
    public function actionPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    //小票打印
    public function actionOrderPrint()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderPrintForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->save());
        }
    }

    //售后订单列表
    public function actionRefund()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderRefundListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('refund');
            }
        }
    }

    //售后订单详情
    public function actionRefundDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->refundDetail());
        } else {
            return $this->render('refund-detail');
        }
    }

    //自提订单列表
    public function actionOffline()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->send_type = 1;
                $form->fields = $fields;
                $form->search();
                return false;
            } else {
                return $this->render('offline');
            }
        }
    }

    // 售后退款订单，商家确认收货
    public function actionShouHuo()
    {
        if (\Yii::$app->request->isPost) {
            $form = new \app\forms\mall\order\OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->shouHuo());
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

    public function actionAddressList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            return $this->asJson($form->addressList());
        }
    }

    /**
     * 订单核销确认收款
     */
    public function actionClerkAffirmPay()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->affirmPay());
    }

    /**
     * 订单核销
     */
    public function actionOrderClerk()
    {
        $form = new OrderClerkForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->OrderClerk());
    }

    public function actionOrderSales()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->orderSales());
        }
    }
}
