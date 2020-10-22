<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\mch\forms\common\PluginMchGoods;
use app\plugins\mch\forms\common\PluginMchOrder;
use yii\helpers\ArrayHelper;

class OrderForm extends Model
{
    public $page;
    public $limit;
    public $status;
    public $mch_id;
    public $order_type = 'order';
    public $refund_status = 0;
    public $keyword;
    public $start_date;
    public $end_date;

    // 订单类型状态
    public $typeOrder = 'order';
    public $typeOrderRefund = 'refund_order';

    public function rules()
    {
        return [
            [['mch_id'], 'required'],
            [['page', 'limit', 'status', 'mch_id', 'refund_status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            ['order_type', 'in', 'range' => [$this->typeOrder, $this->typeOrderRefund]],
            [['keyword', 'start_date', 'end_date'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // 特殊数据验证处理
        $this->checkData();

        // 售后订单列表
        if ($this->order_type == $this->typeOrderRefund) {
            return $this->getRefundOrderList();
        }

        $query = Order::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => $this->mch_id,
        ]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['>=', 'created_at', $this->start_date]);
            $query->andWhere(['<=', 'created_at', $this->end_date]);
        }

        switch ($this->status) {
            case 0:
                $query->andWhere(['cancel_status' => 0]);
                break;
            // 待付款
            case 1:
                // TODO 货到付款订单除外
                $query->andWhere(['is_pay' => 0, 'cancel_status' => 0])->andWhere(['!=', 'pay_type', 2]);
                break;
            // 待发货
            case 2:
                $query->andWhere(['is_send' => 0, 'cancel_status' => 0])->andWhere([
                    'or',
                    ['pay_type' => 2],
                    ['is_pay' => 1]
                ]);
                break;
            // 待收货
            case 3:
                $query->andWhere(['is_send' => 1, 'is_confirm' => 0]);
                break;
            // 待评价
            case 4:
                break;
            // 已取消
            case 6:
                $query->andWhere(['cancel_status' => 1]);
                break;
            // 取消待处理
            case 7:
                $query->andWhere(['cancel_status' => 2]);
                break;
            case 8:
                $query->andWhere(['is_sale' => 1]);
                break;
            default:
                break;
        }

        // 商品名/订单号/收件人名称
        if ($this->keyword) {
            $goodsWarehouseIds = GoodsWarehouse::find()->andWhere(['mall_id' => \Yii::$app->mall->id])
                ->andWhere(['like', 'name', $this->keyword])->select('id');
            $goodsIds = Goods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'goods_warehouse_id' => $goodsWarehouseIds, 'mch_id' => $this->mch_id])->select('id');
            $orderIds = OrderDetail::find()->andWhere(['goods_id' => $goodsIds])->select('order_id')->column();
            $query->andWhere([
                'or',
                ['like', 'order_no', $this->keyword],
                ['like', 'name', $this->keyword],
                ['in', 'id', $orderIds],
            ]);
        }

        $list = $query->with('detail.goods.goodsWarehouse', 'detailExpress', 'clerk')->orderBy(['created_at' => SORT_DESC])->page($pagination)->all();
        $newList = [];
        /** @var Order $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['detail'] = $item->detail ? ArrayHelper::toArray($item->detail) : [];
            foreach ($item->detail as $key => $orderDetail) {
                $goodsInfo = PluginMchGoods::getGoodsData($orderDetail);
                $newItem['detail'][$key]['goods_info'] = $goodsInfo;
            }

            $newItem['is_send_show'] = 1;
            $newItem['is_cancel_show'] = 1;
            $newItem['is_clerk_show'] = 1;
            $newItem['is_confirm_show'] = 1;
            $newItem['detailExpress'] = $item->detailExpress ? ArrayHelper::toArray($item->detailExpress) : [];
            $newItem['clerk'] = $item->clerk ? ArrayHelper::toArray($item->clerk) : [];

            $newItem['action_status'] = $item->getOrderActionStatus($newItem);
            $newList[] = $newItem;
        }

        $orderStatistics = $this->getOrderStatistics();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'cancel_count' => $orderStatistics['cancelCount'],
                'refund_count' => $orderStatistics['refundCount'],
                'handle_count' => $orderStatistics['handleCount'],
            ]
        ];
    }

    /**
     * 售后订单列表
     * @return array
     */
    public function getRefundOrderList()
    {
        try {
            $query = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id,
                'is_delete' => 0,
            ]);
            // 订单状态筛选
            switch ($this->refund_status) {
                case 1:
                    $query->andWhere(['status' => 1]);
                    break;
                case 2:
                    $query->andWhere(['status' => 2, 'is_send' => 0]);
                    break;
                case 3:
                    $query->andWhere(['status' => 2, 'is_send' => 1])->andWhere([
                        'OR',
                        ['is_confirm' => 0, 'type' => 2],
                        ['is_refund' => 0, 'type' => 1],
                    ]);
                    break;
                case 4:
                    $query->andWhere([
                        'OR',
                        ['type' => 1, 'is_confirm' => 1, 'is_refund' => 1],
                        ['type' => 2, 'is_confirm' => 1],
                    ]);
                    break;
            }

            // 商品名/订单号/收件人名称
            if ($this->keyword) {
                $goodsWarehouseIds = GoodsWarehouse::find()->andWhere(['mall_id' => \Yii::$app->mall->id])
                    ->andWhere(['like', 'name', $this->keyword])->select('id');
                $goodsIds = Goods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'goods_warehouse_id' => $goodsWarehouseIds, 'mch_id' => $this->mch_id])->select('id');
                $orderDetailIds = OrderDetail::find()->andWhere(['goods_id' => $goodsIds])->select('id');

                $orderIds = Order::find()->where(['like', 'name', $this->keyword, 'mch_id' => $this->mch_id])->select('id');

                $query->andWhere([
                    'or',
                    ['like', 'order_no', $this->keyword],
                    ['in', 'order_detail_id', $orderDetailIds],
                    ['in', 'order_id', $orderIds],
                ]);
            }

            // 日期筛选
            if ($this->start_date && $this->end_date) {
                $query->andWhere(['>=', 'created_at', $this->start_date]);
                $query->andWhere(['<=', 'created_at', $this->end_date]);
            }

            $list = $query->with(['detail.goods.goodsWarehouse', 'order'])
                ->page($pagination)
                ->orderBy('created_at DESC')
                ->all();

            $newList = [];
            $orderRefund = new OrderRefund();
            /** @var OrderRefund $item */
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                $newItem['status_text'] = $orderRefund->statusText($item);
                $goodsInfo = PluginMchGoods::getGoodsData($item->detail);
                $newItem['detail'][] = ['goods_info' => $goodsInfo];
                try {
                    $newItem['pic_list'] = json_decode($newItem['pic_list'], true);
                } catch (\Exception $exception) {
                    $newItem['pic_list'] = [];
                }

                $newItem['order']['name'] = isset($item->order->name) ? $item->order->name : '';
                $newItem['order']['mobile'] = isset($item->order->mobile) ? $item->order->mobile : '';
                $newItem['order']['address'] = isset($item->order->address) ? $item->order->address : '';

                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination,
                    'address' => PluginMchOrder::getRefundAddress($this->mch_id),
                ],
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

    /**
     * 统计待处理订单数量
     * @return array
     */
    private function getOrderStatistics()
    {
        $cancelCount = Order::find()->where([
            'mch_id' => $this->mch_id,
            'cancel_status' => 2,
            'mall_id' => \Yii::$app->mall->id
        ])->count();

        $refundCount = OrderRefund::find()->where([
            'mch_id' => $this->mch_id,
            'is_confirm' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])
            ->andWhere(['!=', 'status', 3])
            ->count();

        $handleCount = (int)$cancelCount + (int)$refundCount;

        return [
            'cancelCount' => (int)$cancelCount,
            'refundCount' => (int)$refundCount,
            'handleCount' => $handleCount,
        ];
    }

    private function checkData()
    {
        // 日期处理
        if ($this->start_date && $this->end_date) {
            try {
                $this->start_date = date('Y-m-d 00:00:00', strtotime($this->start_date));
                $this->end_date = date('Y-m-d 23:59:59', strtotime($this->end_date));
            } catch (\Exception $exception) {
                $this->start_date = '';
                $this->end_date = '';
            }
        }
    }
}
