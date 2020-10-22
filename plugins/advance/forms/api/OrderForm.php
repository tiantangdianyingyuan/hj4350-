<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\advance\forms\api;

use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\CommonDelivery;
use app\forms\common\order\CommonOrderDetail;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\Order;
use app\plugins\advance\Plugin;
use yii\helpers\ArrayHelper;

class OrderForm extends Model
{
    public $id;
    public $page;

    public function rules()
    {
        return [
            [['id', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = Order::find()->where([
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'sign' => (new Plugin())->getName()
        ])
            ->with(['detail.goods.goodsWarehouse', 'advanceOrder'])
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)->asArray()->all();

        foreach ($list as $lKey => $lItem) {
            foreach ($lItem['detail'] as $dKey => $dItem) {
                $goodsInfo = \Yii::$app->serializer->decode($dItem['goods_info']);
                $picUrl = isset($goodsInfo['goods_attr']['pic_url']) ? $goodsInfo['goods_attr']['pic_url'] : '';
                $coverPic = isset($dItem['goods']['cover_pic']) ? $dItem['goods']['cover_pic'] : '';
                $goodsInfo['goods_attr']['pic_url'] = $picUrl ?: $coverPic;
                $goodsInfo['name'] = $dItem['goods']['goodsWarehouse']['name'];
                $list[$lKey]['detail'][$dKey]['goods_info'] = $goodsInfo;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function detail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new CommonOrderDetail();
            $form->id = $this->id;
            $form->is_detail = 1;
            $form->is_goods = 1;
            $form->is_refund = 1;
            $form->is_array = 1;
            $form->is_store = 1;
            $form->relations = ['detailExpress.expressRelation.orderDetail', 'detailExpressRelation'];
            $form->is_vip_card = 1;
            $order = $form->search();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $goodsNum = 0;
            $memberDeductionPriceCount = 0;
            // 统一商品信息，用于前端展示
            $orderRefund = new OrderRefund();
            foreach ($order['detail'] as $key => &$item) {
                $goodsNum += $item['num'];
                $memberDeductionPriceCount += $item['member_discount_price'];
                $goodsInfo = MallGoods::getGoodsData($item);
                // 售后订单 状态
                if (isset($item['refund'])) {
                    $item['refund']['status_text'] = $orderRefund->statusText($item['refund']);
                }
                $order['detail'][$key]['form_data'] = \yii\helpers\BaseJson::decode($item['form_data']);
                $item['goods_info'] = $goodsInfo;

                $item['is_show_apply_refund'] = 0;

                $refund = OrderRefund::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'order_detail_id' => $item['id']])->orderBy('id DESC')->one();

                if (!$refund && $order['is_sale'] == 0) {
                    $item['is_show_apply_refund'] = 1;
                }
                $item['refund'] = $refund ? ArrayHelper::toArray($refund) : null;
                if ($item['refund']) {
                    // 售后订单 状态
                    $item['refund']['status_text'] = $orderRefund->statusText($item['refund']);
                    $refundList = OrderRefund::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'order_detail_id' => $item['id']])->all();
                    // 售后被拒绝后可再申请一次
                    if ($refund->status == 3 && count($refundList) == 1) {
                        $item['is_show_apply_refund'] = 1;
                    }
                }
            }

            foreach ($order['detailExpress'] as &$detailExpress) {
                foreach ($detailExpress['expressRelation'] as &$expressRelation) {
                    $expressRelation['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation['orderDetail']['goods_info']);
                }
                unset($expressRelation);
            }
            unset($detailExpress);
            // 订单状态
            $order['status_text'] = (new \app\models\Order())->orderStatusText($order);
            $order['pay_type_text'] = (new Order())->getPayTypeText($order['pay_type']);
            // 订单商品总数
            $order['goods_num'] = $goodsNum;
            $order['member_deduction_price_count'] = price_format($memberDeductionPriceCount);
            $order['city'] = json_decode($order['city_info'], true);
            if ($order['send_type'] == 2) {
                $order['delivery_config'] = CommonDelivery::getInstance()->getConfig();
            }

            $plugins = \Yii::$app->plugin->list;
            $order['plugin_data'] = [];
            $newData = [];

            foreach ($plugins as $plugin) {
                $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var \app\core\Plugin $pluginObject */
                if (!class_exists($PluginClass)) {
                    continue;
                }
                $object = new $PluginClass();
                if (method_exists($object, 'getOrderInfo')) {
                    $data = $object->getOrderInfo($order['id'], $order);
                    if ($data && is_array($data)) {
                        foreach ($data as $dIndex => $datum) {
                            if (isset($newData[$dIndex])) {
                                $newData[$dIndex] = array_merge($newData[$dIndex], $datum);
                            } else {
                                $newData[$dIndex] = $datum;
                            }
                        }
                    }
                    $order['plugin_data'] = $newData;
                }
            }

            // 兼容发货方式
            try {
                $order['is_offline'];
            } catch (\Exception $exception) {
                $order['is_offline'] = $order['send_type'];
            }

            $order['advance_order'] = AdvanceOrder::find()->where(['order_id' => $order['id']])->asArray()->one();
            if (!empty($order['advance_order'])) {
                $order['advance_order']['deposit'] *= $order['advance_order']['goods_num'];
                $order['advance_order']['swell_deposit'] *= $order['advance_order']['goods_num'];
                $order['final_price'] = price_format($order['total_goods_original_price'] - $order['advance_order']['swell_deposit']);
            } else {
                $order['final_price'] = price_format($order['total_goods_original_price']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $order
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
