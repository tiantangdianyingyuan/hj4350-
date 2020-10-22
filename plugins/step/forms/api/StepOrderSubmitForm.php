<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderSubmitForm;
use app\models\Goods;
use app\models\OrderDetail;
use app\plugins\step\forms\common\CommonCurrencyModel;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\CommonStepGoods;
use app\plugins\step\models\StepGoodsAttr;
use app\plugins\step\models\StepOrder;
use app\plugins\step\models\StepUser;

class StepOrderSubmitForm extends OrderSubmitForm
{
    public $form_data;

    public function rules()
    {
        return [
            [['form_data'], 'required'],
        ];
    }

    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $newGoodsAttr = parent::getGoodsAttr($goodsAttrId, $goods);
        $stepAttr = CommonStepGoods::getAttr($newGoodsAttr->goods_id, $newGoodsAttr->id, $goods->mall_id);
        $newGoodsAttr->extra = ['step_currency' => $stepAttr->currency];
        return $newGoodsAttr;
    }

    protected function getCustomCurrency($goods, $goodsAttr)
    {
        $stepAttr = CommonStepGoods::getAttr($goodsAttr->goods_id, $goodsAttr->id, $goods->mall_id);
        $name = CommonStep::getSetting()['currency_name'];
        $num = $goodsAttr['number'];
        return [$stepAttr['currency'] * $num . $name];
    }

    //TODO 仅单商品
    protected function getCustomCurrencyAll($listData)
    {
        $num = $listData[0]['goods_list'][0]['num'];
        $id = $listData[0]['goods_list'][0]['goods_attr']['id'];
        $name = CommonStep::getSetting()['currency_name'];

        $stepAttr = StepGoodsAttr::findOne(['attr_id' => $id]);
        return [$stepAttr->currency * $num . $name];
    }

    public function extraOrder($order, $mchItem)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $orderDetail = OrderDetail::find()->where(['order_id' => $order->id])->with('goods')->one();
            if (!$orderDetail) {
                throw new \Exception('订单详情不存在');
            }
            $stepUser = StepUser::findOne([
                'mall_id' => $order->mall_id,
                'user_id' => $order->user_id,
                'is_delete' => 0
            ]);
            if (!$stepUser) {
                throw new \Exception('用户不存在');
            }

            $extra = \Yii::$app->serializer->decode($orderDetail->goods_info)['goods_attr']['extra'];

            // 创建步数宝订单
            $stepOrder = new StepOrder();
            $stepOrder->mall_id = $order->mall_id;
            $stepOrder->token = $order->token;
            $stepOrder->num = $orderDetail->num;
            $stepOrder->user_id = $order->user_id;
            $stepOrder->total_pay_price = $order->total_pay_price;
            $stepOrder->order_id = $order->id;
            $stepOrder->currency = bcmul($extra['step_currency'], $orderDetail->num, 2);
            $stepOrder->is_delete = 0;
            if (!$stepOrder->save()) {
                throw new \Exception('创建步数宝订单失败');
            }

            (new CommonCurrencyModel())->setUser($stepUser)->sub(floatval($stepOrder->currency), $orderDetail->goods->name, '步数宝订单号：' . $order->order_no);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getSendType($mchItem)
    {
        $setting = CommonStep::getSetting();
        if ($setting) {
            $sendType = $setting['send_type'];
        } else {
            $sendType = ['express', 'offline'];
        }
        return $sendType;
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
