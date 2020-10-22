<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\order;

use app\core\response\ApiCode;
use app\forms\common\order\send\CitySendForm;
use app\forms\common\order\send\ExpressSendForm;
use app\forms\common\order\send\NoExpressSendForm;
use app\forms\common\order\send\OtherCitySendForm;
use app\models\Model;
use app\models\Order;

class OrderSendForm extends Model
{
    public $order_id;
    public $is_express;

    public function rules()
    {
        return [
            [['order_id', 'is_express'], 'required'],
            [['order_id', 'is_express'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单ID',
            'is_express' => '发货方式',
        ];
    }

    //发货
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $order = Order::findOne([
                'id' => $this->order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->send_type == 0 || $order->send_type == 1) {
                // 快递配送
                switch ($this->is_express) {
                    // 快递配送
                    case 1:
                        $form = new ExpressSendForm();
                        break;
                    // 自定义物流
                    case 2:
                        $form = new NoExpressSendForm();
                        break;
                    default:
                        throw new \Exception('发货方式异常');
                }
            } elseif ($order->send_type == 2) {
                // 同城配送
                switch ($this->is_express) {
                    // 第三方配送
                    case 1:
                        $form = new OtherCitySendForm();
                        break;
                    // 商家配送
                    case 2:
                        $form = new CitySendForm();
                        break;
                    default:
                        throw new \Exception('发货方式异常');
                }
            } else {
                throw new \Exception('订单数据异常');
            }

            $form->attributes = \Yii::$app->request->post();
            return $form->send();
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }
}
