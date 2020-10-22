<?php

namespace app\plugins\booking\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\common\order\CommonOrderDetail;
use app\forms\common\order\CommonOrderList;
use app\models\Model;
use app\models\Order;
use app\models\OrderRefund;
use app\plugins\booking\forms\common\CommonBookingGoods;

class OrderListForm extends Model
{
    public $mall;
    public $page;
    public $status;
    public $id;

    public function rules()
    {
        return [
            [['page', 'status', 'id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $form = new CommonOrderList();
            $form->status = $this->status;
            $form->sign_id = 'booking';
            $form->is_detail = 1;
            $form->page = $this->page;
            $form->user_id = \Yii::$app->user->id;
            $form->is_array = 1;
            $form->is_refund = 1;
            $form->is_goods = 1;
            if ($form->status == 9) {
                $form->is_cancel_status = false;
            }

            $form->getQuery();
            switch ($form->status) {
                case 2:
                    $form->query->andWhere(['o.cancel_status' => 0]);
                    break;
                case 9:
                    $form->query->andWhere(['<>', 'o.cancel_status', 0]);
                    break;
                default:
                    break;
            }

            $list = $form->query->asArray(true)->groupBy('o.id')->all();

            foreach ($list as $listKey => &$item) {
                if ($item['order_form']) {
                    $item['order_form'] = \Yii::$app->serializer->decode($item['order_form']);
                }
                foreach ($item['detail'] as $detailKey => &$dItem) {
                    $goodsAttrInfo = \Yii::$app->serializer->decode($dItem['goods_info']);
                    $goodsInfo = [
                        'attr_list' => $goodsAttrInfo['attr_list'],
                        'name' => $dItem['goods']['goodsWarehouse']['name'],
                        'num' => $dItem['num'],
                        'total_original_price' => $dItem['total_original_price'],
                        'member_discount_price' => $dItem['member_discount_price'],
                        'pic_url' => $goodsAttrInfo['goods_attr']['pic_url'] ?: $dItem['goods']['goodsWarehouse']['cover_pic']
                    ];
                    $dItem['goods_info'] = $goodsInfo;
                    unset($dItem['goods']);
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
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
            $detail = $form->search();
            if (!$detail) {
                throw new \Exception('订单不存在');
            }
            $goodsNum = 0;
            // 统一商品信息，用于前端展示
            foreach ($detail['detail'] as $key => $item) {
                $goodsNum += $item['num'];
                $goodsInfo['name'] = $item['goods']['goodsWarehouse']['name'];
                $goodsInfo['num'] = $item['num'];
                $goodsInfo['total_original_price'] = $item['total_original_price'];
                $goodsInfo['member_discount_price'] = $item['member_discount_price'];

                // 规格信息json 转 数组
                if ($item['goods_info']) {
                    $goodsAttrInfo = \Yii::$app->serializer->decode($item['goods_info']);
                    $goodsInfo['attr_list'] = $goodsAttrInfo['attr_list'];
                    $picUrl = $goodsAttrInfo['goods_attr']['pic_url'] ?: $item['goods']['goodsWarehouse']['cover_pic'];
                    $goodsInfo['pic_url'] = $picUrl;
                }

                // 售后订单 状态
                if (isset($item['refund'])) {
                    $detail['detail'][$key]['refund']['status_text'] = (new OrderRefund())->statusText($item['refund']);
                }
                $detail['detail'][$key]['goods_info'] = $goodsInfo;
            }
            // 订单状态
            $detail['status_text'] = (new Order())->orderStatusText($detail);
            $detail['pay_type_text'] = (new Order())->getPayTypeText($detail['pay_type']);
            // 订单商品总数
            $detail['goods_num'] = $goodsNum;
            //门店信息
            $bookingGoods = CommonBookingGoods::getGoods($item['goods_id']);
            $detail['store_list'] = $bookingGoods->store;
            //
            if($detail['order_form'] && $detail['order_form'] !== "[]") {
                $detail['order_form'] = \Yii::$app->serializer->decode($detail['order_form']);
            } else {
                $detail['order_form'] = [];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail
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

    public function clerkCode()
    {
        try {
            $qrCode = new CommonQrCode();
            $res = $qrCode->getQrCode(['id' => $this->id], 100, 'pages/order/clerk/clerk');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
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
