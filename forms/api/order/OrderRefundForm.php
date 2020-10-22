<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\forms\common\template\TemplateList;
use app\models\Express;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\mch\models\Mch;
use yii\helpers\ArrayHelper;

class OrderRefundForm extends Model
{
    public $id; // 订单详情ID

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $detail = OrderDetail::find()->andWhere([
                'id' => $this->id,
            ])->with('goods.goodsWarehouse', 'order.detail')->asArray()->one();

            if (!$detail) {
                throw new \Exception('订单不存在');
            }

            $orderDetail = new OrderDetail();
            $goodsAttrInfo = $orderDetail->decodeGoodsInfo($detail['goods_info']);

            $goods = Goods::findOne($detail['goods']['id']);

            $goodsInfo = MallGoods::getGoodsData($detail);
            $detail['goods']['goodsWarehouse']['cover_pic'];
            $detail['goods_info'] = $goodsInfo;
            $detail['refund_price'] = $detail['total_price'] < $detail['order']['total_pay_price'] ?
            $detail['total_price'] : $detail['order']['total_pay_price'];
            $detail['refund_price_text'] = '￥' . $detail['refund_price'];
            $detail['is_confirm'] = $detail['order']['is_confirm'];
            $detail['page_url'] = $goods->getPageUrl();
            //预售尾款订单，售后可退金额为尾款+定金
            if ($detail['goods']['sign'] == 'advance') {
                //判断是否存在插件，是否有插件权限
                $bool = false;
                $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo); //取商城所属账户权限
                if (!is_array($permission_arr) && $permission_arr) {
                    $bool = true;
                } else {
                    foreach ($permission_arr as $value) {
                        if ($value == 'advance') {
                            $bool = true;
                            break;
                        }
                    }
                }
                if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                    $plugin = \Yii::$app->plugin->getPlugin('advance');
                    /* @var AdvanceOrder $advance_info */
                    $advance_info = $plugin->getAdvance($detail['order']['id'], $detail['order']['order_no']);
                    if (!empty($advance_info)) {
                        $detail['refund_price'] += bcmul($advance_info->goods_num, $advance_info->deposit);
                    }
                }
            }
            $detail['template_message_list'] = $this->getTemplateMessage();
            $priceList[] = [
                'label' => '小计',
                'value' => $detail['total_price'],
            ];
            $detail['plugin_data'] = (new Order())->getPluginData($detail['order'], $priceList);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'list' => $this->getTextList($detail['is_confirm']),
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

    private function getTextList($isConfirm)
    {
        return [
            'goods_status' => [
                '未收到货',
                '已收到货',
            ],
            'refund_list' => [
                'confirm_list' => [
                    '质量问题',
                    '拍错/多拍/不喜欢',
                    '商品描述不符',
                    '假货',
                    '商家发错货',
                    '商品破损/少件',
                    '其它',
                ],
                'not_confirm_list' => [
                    '多买/买错/不想要',
                    '快递无记录',
                    '少货/空包裹',
                    '未按时间发货',
                    '快递一直未送达',
                    '其它',
                ],
            ],
            'refund_list_2' => [
                '包装/商品破损',
                '使用后过敏',
                '功能/效果与商品描述不符',
                '少件/漏发',
                '版本/批次/颜色/容量等与商品描述不符',
                '做工问题',
                '卖家发错货',
                '商品变质/发霉/有异物',
                '假冒品牌',
                '生产日期/保质期与商品描述不符',
                '其它',
            ],
        ];
    }

    private function getTemplateMessage()
    {
        $arr = ['order_refund_tpl'];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
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

        /** @var OrderRefund $orderRefund */
        $orderRefund = OrderRefund::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])
            ->with('detail.goods.goodsWarehouse', 'order', 'refundAddress')
            ->one();

        if (!$orderRefund) {
            throw new \Exception('订单不存在');
        }

        $newOrderRefund = ArrayHelper::toArray($orderRefund);
        $newOrderRefund['goods_info'] = MallGoods::getGoodsData($orderRefund->detail);
        $newOrderRefund['status_text'] = $orderRefund->statusText($orderRefund);
        $newOrderRefund['hint_text'] = $orderRefund->hintText($orderRefund);
        $newOrderRefund['refund_type_text'] = $orderRefund->getRefundTypeText($orderRefund);
        $newOrderRefund['pic_list'] = $this->getPicList($orderRefund);
        $newOrderRefund['template_message_list'] = $this->getTemplateMessage();
        $newOrderRefund['send_type'] = $orderRefund->order->send_type;
        $newOrderRefund = array_merge($newOrderRefund, $orderRefund->checkAfterRefund($orderRefund));
        $newOrderRefund = array_merge($newOrderRefund, $this->getRefundData($orderRefund));
        $newOrderRefund = array_merge($newOrderRefund, $this->getRefundAddress($orderRefund));

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newOrderRefund,
                'express_list' => Express::getExpressList(),
            ],
        ];
    }

    /**
     * @param OrderRefund $orderRefund
     * @return array
     */
    private function getRefundData($orderRefund)
    {
        try {
            $refundData = json_decode($orderRefund->refund_data, true);
        } catch (\Exception $exception) {
            $refundData = [];
        }

        $platform = '平台自营';
        if ($orderRefund->mch_id > 0) {
            /** @var Mch $mch */
            $mch = Mch::find()->andWhere(['id' => $orderRefund->mch_id])->with('store')->one();
            $platform = $mch && $mch->store ? $mch->store->name : '未知商户';
        }

        return [
            'refund_data' => [
                'goods_status' => isset($refundData['goods_status']) ? $refundData['goods_status'] : '',
                'cause' => isset($refundData['cause']) ? $refundData['cause'] : '',
            ],
            'platform' => $platform,
        ];
    }

    /**
     * @param OrderRefund $orderRefund
     * @return  array
     */
    private function getRefundAddress($orderRefund)
    {
        $refundAddress = '';
        if ($orderRefund->refundAddress) {
            try {
                $orderRefund->refundAddress->address = \Yii::$app->serializer->decode($orderRefund->refundAddress->address);
            } catch (\Exception $exception) {
                $orderRefund->refundAddress->address = [];
            }
            $address = '';
            foreach ($orderRefund->refundAddress->address as $item) {
                $address .= $item;
            }
            $refundAddress = $address . $orderRefund->refundAddress->address_detail;

        }

        $array = [
            'refund_address' => $refundAddress,
            'refund_name' => $orderRefund->refundAddress ? $orderRefund->refundAddress->name : '',
            'refund_mobile' => $orderRefund->refundAddress ? $orderRefund->refundAddress->mobile : '',
            'refund_remark' => $orderRefund->refundAddress ? $orderRefund->refundAddress->remark : '',
        ];

        return $array;
    }

    /**
     * @param OrderRefund $orderRefund
     * @return array|mixed
     */
    private function getPicList($orderRefund)
    {
        try {
            $picList = json_decode($orderRefund->pic_list);
        } catch (\Exception $exception) {
            $picList = [];
        }

        return $picList;
    }
}
