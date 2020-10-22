<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api\v2;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserInfo;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class OrderForm extends Model
{
    public $goods_id;
    public $id;
    public $pintuan_status;

    public function rules()
    {
        return [
            [['goods_id', 'id', 'pintuan_status'], 'integer'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = PintuanOrderRelation::find()->where(['is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_groups' => 1]);
        $orderQuery = Order::find()->where([
            'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ]);

        switch ($this->pintuan_status) {
            // 待付款
            case 1:
                $pintuanOrderIds = PintuanOrders::find()
                    ->where(['mall_id' => \Yii::$app->mall->id])
                    ->andWhere(['status' => [0, 1]])
                    ->select('id');
                $orderIds = PintuanOrderRelation::find()
                    ->where(['pintuan_order_id' => $pintuanOrderIds])
                    ->select('order_id');
                $orderIds = $orderQuery
                    ->andWhere(['is_pay' => 0, 'id' => $orderIds])
                    ->andWhere(['!=', 'pay_type', 2])
                    ->andWhere(['!=', 'cancel_status', 1])
                    ->select('id');
                $query->andWhere(['order_id' => $orderIds]);
                break;
            // 拼团中
            case 2:
                $pintuanOrderIds = PintuanOrders::find()
                    ->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id])
                    ->select('id');
                $orderIds = PintuanOrderRelation::find()
                    ->where(['pintuan_order_id' => $pintuanOrderIds])
                    ->select('order_id');
                $orderIds = $orderQuery
                    ->andWhere(['id' => $orderIds])
                    ->andWhere([
                        'or',
                        ['is_pay' => 1],
                        ['pay_type' => 2],
                    ])
                    ->select('id');
                $query->andWhere(['order_id' => $orderIds]);
                break;
            // 拼团成功
            case 3:
                $pintuanOrderIds = PintuanOrders::find()
                    ->where(['status' => 2, 'mall_id' => \Yii::$app->mall->id])
                    ->select('id');
                $orderIds = PintuanOrderRelation::find()
                    ->where(['pintuan_order_id' => $pintuanOrderIds])
                    ->select('order_id');
                $orderIds = $orderQuery
                    ->andWhere(['id' => $orderIds])
                    ->andWhere([
                        'or',
                        ['is_pay' => 1],
                        ['pay_type' => 2],
                    ])
                    ->select('id');
                $query->andWhere(['order_id' => $orderIds]);
                break;
            // 拼团失败
            case 4:
                $pintuanOrderIds = PintuanOrders::find()
                    ->where(['status' => 3, 'mall_id' => \Yii::$app->mall->id])
                    ->select('id');
                $orderIds = PintuanOrderRelation::find()
                    ->where(['pintuan_order_id' => $pintuanOrderIds])
                    ->select('order_id');
                $query->andWhere(['order_id' => $orderIds]);
                break;
            default:
                $orderIds = $orderQuery->andWhere(['!=', 'cancel_status', 1])->select('id');
                $query->andWhere(['order_id' => $orderIds]);
                break;
        }
        $list = $query
            ->with('order.orderDetail', 'pintuanOrder.goods.goodsWarehouse', 'user.userInfo')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var PintuanOrderRelation $item */
        foreach ($list as $item) {
            if ($item->pintuanOrder) {
                $newItem = [];
                // 拼团限时结束时间 从团长的支付时间开始计算
                /** @var PintuanOrderRelation $orderRelation */
                $orderRelation = PintuanOrderRelation::find()
                    ->where(['pintuan_order_id' => $item->pintuan_order_id, 'is_groups' => 1, 'is_parent' => 1])
                    ->with('order')
                    ->one();

                $endTime = (strtotime($orderRelation->order->pay_time) + $item->pintuanOrder->pintuan_time * 60 * 60);
                $newItem['end_date_time'] = date('Y-m-d H:i:s', $endTime);
                $newItem['detail'] = [];
                $newItem['id'] = $item->order->id;
                $newItem['pintuan_order_id'] = $item->pintuanOrder->id;
                $newItem['status'] = $item->pintuanOrder->status;
                $newItem['status_cn'] = $item->pintuanOrder->getStatusText($item->pintuanOrder);
                $newItem['total_pay_price'] = $item->order->total_pay_price;
                $newItem['pay_type'] = $item->order->pay_type;
                $newItem['is_pay'] = $item->order->is_pay;

                foreach ($item->order->orderDetail as $orderDetail) {
                    $goodsInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
                    $newGoodsInfo = [];
                    $newGoodsInfo['attr_list'] = $goodsInfo['attr_list'];
                    $goodsAttr = $goodsInfo['goods_attr'];
                    $newGoodsInfo['name'] = $goodsAttr['name'] ?: $item->pintuanOrder->goods->name;
                    $newGoodsInfo['cover_pic'] = $goodsAttr['cover_pic'] ?: $item->pintuanOrder->goods->coverPic;
                    $newItem['detail'][] = [
                        'goods_info' => $newGoodsInfo,
                        'num' => $orderDetail->num,
                        'total_price' => $orderDetail->total_price,
                    ];
                }
                $newList[] = $newItem;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ],
        ];
    }

    public function getPintuanDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /** @var PintuanOrders $pintuanOrder */
            $pintuanOrder = PintuanOrders::find()
                ->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id])
                ->with('goods.goodsWarehouse', 'orderRelation.user.userInfo', 'orderRelation.robot', 'orderRelation.order')
                ->with('groupOrderRelation.order')
                ->one();

            if (!$pintuanOrder) {
                throw new \Exception('拼团详情不存在');
            }

            $newOrder = [];
            $newOrder['id'] = $pintuanOrder->id;
            $newOrder['status'] = $pintuanOrder->status;
            $newOrder['status_cn'] = $pintuanOrder->getStatusText($pintuanOrder);
            $newOrder['goods_name'] = $pintuanOrder->goods->name;
            $newOrder['goods_cover_pic'] = $pintuanOrder->goods->coverPic;
            $newOrder['original_price'] = $pintuanOrder->goods->originalPrice;
            $newOrder['people_num'] = $pintuanOrder->people_num;
            $newOrder['goods_id'] = $pintuanOrder->goods->id;
            $newOrder['app_share_title'] = $pintuanOrder->goods->app_share_title;
            $newOrder['app_share_pic'] = $pintuanOrder->goods->app_share_pic;
            $newOrder['is_join'] = 0;
            $newOrder['group_id'] = $pintuanOrder->pintuan_goods_groups_id;
            $groupUsers = []; // 拼团成员信息
            /** @var PintuanOrderRelation $item */
            foreach ($pintuanOrder->orderRelation as $item) {
                // 未取消|已支付的订单才算
                if ($item->cancel_status == 0 && ($item->order && $item->order->is_pay == 1 || $item->order && $item->order->pay_type == 2) || $item->robot_id > 0) {
                    $newItem = [];
                    $newItem['id'] = $item->id;
                    // 是否为团长
                    $newItem['is_parent'] = $item['is_parent'];
                    if ($item->user_id == \Yii::$app->user->id) {
                        $newOrder['is_join'] = 1;
                    }
                    // 机器人信息
                    if ($item->robot_id) {
                        // 兼容 robot_id 目前存的是user_id
                        if ($item->robot) {
                            $newItem['avatar'] = $item->robot->avatar;
                        } else {
                            /** @var UserInfo $userInfo */
                            $userInfo = UserInfo::findOne(['user_id' => $item->robot_id]);
                            $newItem['avatar'] = $userInfo ? $userInfo->avatar : '';
                        }
                    } else {
                        $newItem['avatar'] = $item->user->userInfo->avatar;
                    }
                    $groupUsers[] = $newItem;
                }
            }
            $newOrder['group_users'] = $groupUsers;

            // TODO 这里商品可能会被删除
            /** @var Goods $goods */
            $goods = Goods::find()
                ->where(['id' => $pintuanOrder->goods_id, 'mall_id' => \Yii::$app->mall->id])
                ->with('groups.goods', 'attr')
                ->one();

            if (!$goods) {
                throw new \Exception($this->getErrorMsg('商品不存在'));
            }

            $goodsIds = PintuanGoods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goods->id])
                ->select('goods_id');
            $goodsList = Goods::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $goodsIds])
                ->with('groups')->all();

            $groupMinPrice = 0;
            /** @var Goods $goods */
            foreach ($goodsList as $goods) {
                foreach ($goods->attr as $aItem) {
                    $groupMinPrice = $groupMinPrice == 0 ? $aItem->price : min($groupMinPrice, $aItem->price);
                }
            }

            $newOrder['price'] = $groupMinPrice;
            // 拼团省价格
            $newOrder['group_economize_price'] = price_format($pintuanOrder->goods->goodsWarehouse->original_price - $groupMinPrice);
            $payTime = $pintuanOrder->groupOrderRelation->order->pay_time;
            $pintuanTime = strtotime($payTime) + $pintuanOrder->pintuan_time * 60 * 60;
            // 拼团剩余所需人数
            $newOrder['surplus_people'] = (int) ($pintuanOrder->people_num - count($groupUsers));
            // 拼团结束时间
            $newOrder['surplus_date_time'] = date('Y-m-d H:i:s', $pintuanTime);

            $newOrder['one_goods_id'] = $this->getOnePintuanGoodsId($goods);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newOrder,
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }
    // 获取拼团单独购买的商品ID
    private function getOnePintuanGoodsId($goods)
    {
        $pintuanGoods = PintuanGoods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'goods_id' => $goods->id])->one();
        if (!$pintuanGoods) {
            throw new \Exception('参团商品不存在');
        }

        $goodsId = $pintuanGoods->goods_id;
        if ($pintuanGoods->pintuan_goods_id > 0) {
            $pintuanGoods = PintuanGoods::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $pintuanGoods->pintuan_goods_id])->one();
            if (!$pintuanGoods) {
                throw new \Exception('参团商品异常');
            }
            $goodsId = $pintuanGoods->goods_id;
        }

        return $goodsId;
    }
}
