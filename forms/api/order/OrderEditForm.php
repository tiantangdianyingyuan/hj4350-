<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\Plugin;
use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\CommonDelivery;
use app\forms\common\order\CommonOrderDetail;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\Order;
use app\models\orderDetail;
use app\models\OrderRefund;
use app\plugins\mch\models\Mch;
use yii\helpers\ArrayHelper;

class OrderEditForm extends Model
{
    public $id; // 订单ID
    public $action_type; //操作订单的类型,1 订单核销详情|

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['action_type'], 'string'],
        ];
    }

    public function getDetail()
    {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }

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
            $order['is_show_send_type'] = 1;
            $order['is_can_apply_sales'] = 1;
            $order['is_show_express'] = 0;
            $priceList = [];
            foreach ($order['detail'] as $key => &$item) {
                $goodsNum += $item['num'];
                $memberDeductionPriceCount += $item['member_discount_price'];
                $goodsInfo = MallGoods::getGoodsData($item);
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

                $form_data = empty($item['form_data']) ? "[]" : $item['form_data'];
                $item['form_data'] = \Yii::$app->serializer->decode($form_data);
                $item['goods_info'] = $goodsInfo;
                $order['is_show_send_type'] = $goodsInfo['is_show_send_type'];
                $order['is_can_apply_sales'] = $goodsInfo['is_can_apply_sales']; // 是否显示售后按钮
                $order['is_show_express'] = $order['is_show_express'] || $goodsInfo['is_show_express'] ? 1 : 0; // 展示运费（只要有一个商品支持展示运费就需要展示）
                $order['goods_type'] = $goodsInfo['goods_type']; // 商品类型

                $priceList[] = [
                    'label' => '小计',
                    'value' => $item['total_price'],
                ];
            }

            $merchantRemarkList = [];
            foreach ($order['detailExpress'] as &$detailExpress) {
                if ($detailExpress['send_type'] == 1 && $detailExpress['merchant_remark']) {
                    $merchantRemarkList[] = $detailExpress['merchant_remark'];
                }

                $goodsNum = 0;
                foreach ($detailExpress['expressRelation'] as &$expressRelation) {
                    $goodsNum += $expressRelation['orderDetail']['num'];
                    $expressRelation['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation['orderDetail']['goods_info']);
                }
                $detailExpress['goods_num'] = $goodsNum;
                unset($expressRelation);
            }
            unset($detailExpress);
            $order['merchant_remark_list'] = $merchantRemarkList;

            // 订单状态
            $order['status_text'] = (new Order())->orderStatusText($order);
            $order['pay_type_text'] = (new Order())->getPayTypeText($order['pay_type']);
            // 订单商品总数
            $order['goods_num'] = $goodsNum;
            $order['member_deduction_price_count'] = price_format($memberDeductionPriceCount);
            $order['city'] = json_decode($order['city_info'], true);
            if ($order['send_type'] == 2) {
                $order['delivery_config'] = CommonDelivery::getInstance()->getConfig();
            }

            $order['plugin_data'] = (new Order())->getPluginData($order, $priceList);
            $order['plugin_data'] = (new orderDetail())->changePluginData($order['plugin_data']);
            try {
                if ($order['sign']) {
                    $PluginClass = 'app\\plugins\\' . $order['sign'] . '\\Plugin';
                    /** @var Plugin $pluginObject */
                    $object = new $PluginClass();
                    if (method_exists($object, 'changeOrderInfo')) {
                        $order = $object->changeOrderInfo($order);
                    }
                }
            } catch (\Exception $exception) {
            }

            // 商品类型
            $typeData = [];
            $typePlugin = \Yii::$app->plugin->getAllTypePlugins();
            foreach ($typePlugin as $name => $plugin) {
                if (method_exists($plugin, 'getTypeData')) {
                    $typeData[$name] = $plugin->getTypeData($order);
                }
            }
            $order['type_data'] = $typeData;

            // 兼容发货方式
            try {
                $order['is_offline'];
            } catch (\Exception $exception) {
                $order['is_offline'] = $order['send_type'];
            }

            $order['template_message_list'] = $this->getTemplateMessage();

            try {
                $order['cancel_data'] = json_decode($order['cancel_data'], true);
            } catch (\Exception $exception) {
                $order['cancel_data'] = [];
            }
            $order['platform'] = '平台自营';
            if ($order['mch_id']) {
                /** @var Mch $mch */
                $mch = Mch::find()->where(['id' => $order['mch_id']])->with('store')->one();
                $order['platform'] = $mch && $mch->store ? $mch->store->name : '未知商户';
            }
            $order['refund_price_text'] = '￥' . $order['total_pay_price'];

            //预约表单
            $order['order_form'] = \yii\helpers\BaseJson::decode($order['order_form']);
            // TODO 兼容旧的同城配送订单
            $order['detailExpress'] = $this->getOldCityDetailExpress($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $order,
                ],
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

    private function getTemplateMessage()
    {
        $arr = ['order_cancel_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }

    private function getOldCityDetailExpress($order)
    {
        if (!empty($order['detailExpress'])) {
            return $order['detailExpress'];
        }

        if (!$order['city_name'] && !$order['city_mobile']) {
            return $order['detailExpress'];
        }

        // 兼容旧版本同城配送订单数据
        $expressRelation = [];
        foreach ($order['detail'] as $key => $item) {
            $expressRelation[] = [
                'orderDetail' => $item,
            ];
        }
        $detailExpress = [];
        $detailExpress[] = [
            'city_name' => $order['city_name'],
            'city_mobile' => $order['city_mobile'],
            'goods_num' => count($order['detail']),
            'expressRelation' => $expressRelation,
        ];

        return $detailExpress;
    }
}
