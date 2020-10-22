<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\order\CommonOrder;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\models\Model;
use app\models\Order;

class OrderPrintForm extends Model
{
    public $order_id;
    public $print_id = 0;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'print_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $printOrder = new PrintOrder();
            $order = Order::findOne(['is_delete' => 0, 'id' => $this->order_id, 'mall_id' => \Yii::$app->mall->id]);
            $orderConfig = CommonOrder::getCommonOrder($order->sign === 'mch' ? '' : $order->sign)->getOrderConfig();
            if ($orderConfig->is_print == 0) {
                throw new PrintException('未开启打印设置，无法打印');
            }
            $data = $printOrder->print($order, $this->order_id, 'reprint', $this->print_id);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $data
            ];
        } catch (PrintException $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
