<?php

namespace app\forms\common\order\send;

use app\core\response\ApiCode;
use app\forms\common\order\send\BaseSend;
use app\models\Order;

class NoExpressSendForm extends BaseSend
{
    public $express_content; // 物流内容

    public function rules()
    {
        return array_merge(parent::rules(), [
            // [['express_content'], 'required'], //小程序端目前没有传该字段
            [['express_content'], 'string'],
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $order = $this->getOrder();

            // 兼容小程序端无需物流发货
            if (!$this->order_detail_id) {
                $this->getOrderDetailId();
            }

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

    // 兼容小程序端 无需物流发货
    private function getOrderDetailId()
    {
        $order = Order::find()->where([
            'id' => $this->order_id,
        ])
            ->with('detail', 'detailExpressRelation')
            ->one();

        if (!$order) {
            throw new \Exception('订单不存在');
        }
        $orderDetailId = [];
        foreach ($order->detailExpressRelation as $item) {
            $orderDetailId[] = $item->order_detail_id;
        }
        $sendOrderDetailId = [];
        foreach ($order->detail as $item) {
            if (!in_array($item->id, $orderDetailId)) {
                $sendOrderDetailId[] = $item->id;
            }
        }
        $this->order_detail_id = $sendOrderDetailId;
        $this->express_content = '手机端无需物流发货';
    }

    public function saveExtraData($orderDetailExpress)
    {
        $orderDetailExpress->send_type = 2;
        $orderDetailExpress->express_content = $this->express_content ?: '手机端无需物流发货';
    }
}
