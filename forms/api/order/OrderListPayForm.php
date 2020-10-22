<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;


use app\core\response\ApiCode;
use app\models\Order;

class OrderListPayForm extends OrderPayFormBase
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }

        try {
            $order = Order::find()->where([
                'id' => $this->id,
                'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'is_pay' => 0,
                'cancel_status' => 0,
                'is_confirm' => 0,
                'is_sale' => 0,
            ])->one();

            if (!$order) {
                throw new \Exception('订单数据异常,无法支付');
            }

            return $this->getReturnData([$order]);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
