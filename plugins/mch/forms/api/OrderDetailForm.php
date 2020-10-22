<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\order\CommonOrderDetail;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\mch\forms\common\PluginMchGoods;
use yii\helpers\ArrayHelper;

class OrderDetailForm extends Model
{
    public $mch_id;
    public $id;

    public function rules()
    {
        return [
            [['mch_id', 'id'], 'required'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new CommonOrderDetail();
            $form->mch_id = $this->mch_id;
            $form->id = $this->id;
            $form->is_detail = 1;
            $form->is_goods = 1;
            $form->relations = ['clerk', 'detailExpress'];
            /** @var Order $order */
            $order = $form->search();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $newOrder = ArrayHelper::toArray($order);
            $newOrder['is_send_show'] = 1;
            $newOrder['is_cancel_show'] = 1;
            $newOrder['is_clerk_show'] = 1;
            $newOrder['is_confirm_show'] = 1;
            $newOrder['detailExpress'] = $order->detailExpress ? ArrayHelper::toArray($order->detailExpress) : [];
            $newOrder['clerk'] = $order->clerk ? ArrayHelper::toArray($order->clerk) : [];

            $newOrder['action_status'] = $order->getOrderActionStatus($newOrder);

            // 订单状态
            $newOrder['status_text'] = (new Order())->orderStatusText($order);
            $newOrder['pay_type_text'] = (new Order())->getPayTypeText($order->pay_type);
            $goodsNum = 0;
            $orderRefund = new OrderRefund();
            /** @var OrderDetail $orderDetail */
            foreach ($order->detail as $orderDetail) {
                $newItem = ArrayHelper::toArray($orderDetail);
                $goodsNum += $orderDetail->num;
                $goodsInfo = PluginMchGoods::getGoodsData($orderDetail);
                $newItem['form_data'] = \yii\helpers\BaseJson::decode($newItem['form_data']);
                // 售后订单 状态
                if ($orderDetail->refund) {
                    $newItem['refund'] = ArrayHelper::toArray($orderDetail->refund);
                    $newItem['refund']['status_text'] = $orderRefund->statusText($orderDetail->refund);
                }
                $newItem['goods_info'] = $goodsInfo;
                $newOrder['detail'][] = $newItem;
            }
            // 订单商品总数
            $newOrder['goods_num'] = $goodsNum;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newOrder
                ]
            ];
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