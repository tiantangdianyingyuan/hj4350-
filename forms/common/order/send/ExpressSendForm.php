<?php

namespace app\forms\common\order\send;

use app\core\response\ApiCode;
use app\forms\common\order\send\BaseSend;
use app\models\Order;
use app\models\OrderExpressSingle;

class ExpressSendForm extends BaseSend
{
    public $customer_name; // 京东物流特殊要求字段，商家编码
    public $express;
    public $express_no;
    public $express_single_id; // 电子面单ID
    public $merchant_remark;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['express', 'express_no'], 'required'],
            [['customer_name', 'express', 'express_no', 'merchant_remark'], 'string'],
            [['express_single_id'], 'safe'],
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (substr_count($this->express, '京东') && empty($this->customer_name)) {
                throw new \Exception('京东物流必须填写京东商家编码');
            }

            // TODO 兼容小程序端 小程序端需优化
            if ($this->express_single_id == 'undefined') {
                $this->express_single_id = 0;
            }

            $order = $this->getOrder();
            // 验证快递名称
            $order->validateExpress($this->express);
            //物流发货 需全部商品都已发货 is_send 才改为1
            $this->saveOrderDetailExpress($order);

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功',
            ];

        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    public function saveExtraData($orderDetailExpress)
    {
        $orderDetailExpress->express = $this->express;
        $orderDetailExpress->express_no = $this->express_no;
        $orderDetailExpress->send_type = 1;
        $orderDetailExpress->merchant_remark = $this->merchant_remark ?: '';
        $orderDetailExpress->customer_name = $this->customer_name ?: '';
        // 物流单号对应电子面单ID
        $expressSingle = OrderExpressSingle::findOne($this->express_single_id);
        if ($expressSingle) {
            $expressSingleOrder = \Yii::$app->serializer->decode($expressSingle->order);
            if ($expressSingleOrder->LogisticCode == $this->express_no) {
                $orderDetailExpress->express_single_id = $this->express_single_id;
            }
        }
    }
}
