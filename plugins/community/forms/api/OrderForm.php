<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\forms\common\order\CommonOrder;
use app\models\Model;
use app\models\Order;
use app\plugins\community\models\CommunityOrder;

class OrderForm extends Model
{
    public $id; // 订单ID


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id'], 'required'],
        ];
    }


    public function orderConfirm()
    {
        try {
            $middleman_order_info = CommunityOrder::find()->where([
                'order_id' => $this->id,
                'middleman_id' => \Yii::$app->user->id,
                'is_delete' => 0
            ])->one();

            if (!$middleman_order_info) {
                throw new \Exception('社区订单数据异常');
            }

            /* @var Order $order */
            $order = Order::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $middleman_order_info->user_id,
                'is_delete' => 0,
            ])->one();

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,无法确认收货');
            }

            CommonOrder::getCommonOrder($order->sign)->confirm($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }
}
