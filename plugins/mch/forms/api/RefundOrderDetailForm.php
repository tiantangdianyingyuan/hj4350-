<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;


use app\core\response\ApiCode;
use app\forms\common\order\CommonOrderRefundDetail;
use app\models\Express;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\mch\forms\common\PluginMchGoods;
use app\plugins\mch\forms\common\PluginMchOrder;
use yii\helpers\ArrayHelper;

class RefundOrderDetailForm extends Model
{
    public $mch_id;
    public $id;

    public function rules()
    {
        return [
            [['mch_id', 'id'], 'required'],
        ];
    }

    /**
     * 售后订单详情
     * @return array
     * @throws \Exception
     */
    public function getOrderRefundDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->alias('o')->where([
                'o.mall_id' => \Yii::$app->mall->id,
                'o.id' => $this->id,
                'o.is_delete' => 0,
                'o.mch_id' => $this->mch_id
            ])
                ->with('detail.goods.goodsWarehouse', 'order', 'refundAddress')
                ->one();

            if (!$orderRefund) {
                throw new \Exception('订单不存在');
            }

            $newOrderRefund = ArrayHelper::toArray($orderRefund);
            $newOrderRefund['status_text'] = (new OrderRefund())->statusText($orderRefund);
            $newItem = ArrayHelper::toArray($orderRefund->detail);
            $goodsInfo = PluginMchGoods::getGoodsData($orderRefund->detail);
            $newItem['goods_info'] = $goodsInfo;
            $newOrderRefund['detail'][] = $newItem;

            if ($orderRefund->refundAddress) {
                $newOrderRefund['refundAddress'] = ArrayHelper::toArray($orderRefund->refundAddress);
                try {
                    $orderRefund->refundAddress->address = \Yii::$app->serializer->decode($orderRefund->refundAddress->address);
                }catch (\Exception $exception) {
                    $orderRefund->refundAddress->address = [];
                }
                $refundAddress = '';
                foreach ($orderRefund->refundAddress->address as $item) {
                    $refundAddress .= $item;
                }
                $newOrderRefund['refundAddress']['address'] = $refundAddress . $orderRefund->refundAddress->address_detail;
            }

            $newOrderRefund['order']['name'] = isset($orderRefund->order->name) ? $orderRefund->order->name : '';
            $newOrderRefund['order']['mobile'] = isset($orderRefund->order->mobile) ? $orderRefund->order->mobile : '';
            $newOrderRefund['order']['address'] = isset($orderRefund->order->address) ? $orderRefund->order->address : '';

            try {
                $newOrderRefund['pic_list'] = json_decode($newOrderRefund['pic_list']);
            } catch (\Exception $exception) {
                $newOrderRefund['pic_list'] = [];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newOrderRefund,
                    'express_list' => Express::getExpressList(),
                    'address' => PluginMchOrder::getRefundAddress($this->mch_id),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}