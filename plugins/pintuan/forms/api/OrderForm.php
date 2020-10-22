<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;
use yii\helpers\ArrayHelper;

class OrderForm extends Model
{
    public $page;
    public $goods_id;
    public $id;
    public $pintuan_status;

    public function rules()
    {
        return [
            [['page'], 'safe'],
            [['goods_id', 'id', 'pintuan_status'], 'integer'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        $query = PintuanOrderRelation::find()->where([
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
            'is_groups' => 1
        ]);

        $orderQuery = Order::find()->where([
            'sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ]);

        if ($this->pintuan_status == 1) {
            $pintuanOrderIds = PintuanOrders::find()->where([
                // 'status' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])->andWhere(['status' => [0, 1]])->select('id');
            $orderIds = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $pintuanOrderIds
            ])->select('order_id');
            // 待付款
            $orderIds = $orderQuery->andWhere(['is_pay' => 0, 'id' => $orderIds])
                ->andWhere(['!=', 'pay_type', 2])
                ->andWhere(['!=', 'cancel_status', 1])
                ->select('id');

            $query->andWhere(['order_id' => $orderIds]);
        } elseif ($this->pintuan_status == 2) {
            // 拼团中
            $pintuanOrderIds = PintuanOrders::find()->where([
                'status' => 1,
                'mall_id' => \Yii::$app->mall->id
            ])->select('id');

            $orderIds = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $pintuanOrderIds
            ])->select('order_id');
            $orderIds = $orderQuery->andWhere([
                'id' => $orderIds,
            ])->andWhere([
                'or',
                ['is_pay' => 1],
                ['pay_type' => 2]
            ])->select('id');

            $query->andWhere(['order_id' => $orderIds]);
        } elseif ($this->pintuan_status == 3) {
            // 拼团成功
            $pintuanOrderIds = PintuanOrders::find()->where([
                'status' => 2,
                'mall_id' => \Yii::$app->mall->id
            ])->select('id');

            $orderIds = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $pintuanOrderIds
            ])->select('order_id');

            $orderIds = $orderQuery->andWhere([
                'id' => $orderIds,
            ])->andWhere([
                'or',
                ['is_pay' => 1],
                ['pay_type' => 2]
            ])->select('id');

            $query->andWhere(['order_id' => $orderIds]);
        } elseif ($this->pintuan_status == 4) {
            // 拼团失败
            $pintuanOrderIds = PintuanOrders::find()->where([
                'status' => 3,
                'mall_id' => \Yii::$app->mall->id
            ])->select('id');

            $orderIds = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $pintuanOrderIds
            ])->select('order_id');
//            $orderIds = $orderQuery->andWhere([
//                'id' => $orderIds,
//            ])->andWhere([
//                'or',
//                ['is_pay' => 1],
//                ['pay_type' => 2]
//            ])->select('id');

            $query->andWhere(['order_id' => $orderIds]);
        } else {
            $orderIds = $orderQuery->andWhere(['!=', 'cancel_status', 1])->select('id');
            $query->andWhere(['order_id' => $orderIds]);
        }

        $list = $query->with('order.orderDetail', 'pintuanOrder.goods.goodsWarehouse', 'user.userInfo')
            ->orderBy(['created_at' => SORT_DESC])->page($pagination)->asArray()->all();

        foreach ($list as &$item) {
            if ($item['pintuanOrder']) {
                // 拼团限时结束时间 从团长的支付时间开始计算
                /** @var PintuanOrderRelation $pintuanOrderRelation */
                $pintuanOrderRelation = PintuanOrderRelation::find()->where([
                    'pintuan_order_id' => $item['pintuan_order_id'],
                    'is_groups' => 1,
                    'is_parent' => 1
                ])->with('order')->one();

                $endTime = (strtotime($pintuanOrderRelation->order->pay_time) + $item['pintuanOrder']['pintuan_time'] * 60 * 60);
                $newEndTime = $endTime - time() > 0 ? $endTime - time() : 0;
                $item['pintuanOrder']['end_time'] = $newEndTime;
                $item['pintuanOrder']['end_date_time'] = date('Y-m-d H:i:s', $endTime);
                $item['pintuanOrder']['goods']['attr_groups'] =
                    \Yii::$app->serializer->decode($item['pintuanOrder']['goods']['attr_groups']);
                $item['pintuanOrder']['goods']['pic_url'] =
                    \Yii::$app->serializer->decode($item['pintuanOrder']['goods']['goodsWarehouse']['pic_url']);

                foreach ($item['order']['orderDetail'] as &$dItem) {
                    $dItem['goods_info'] = \Yii::$app->serializer->decode($dItem['goods_info']);
                }
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


    public function getPintuanList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = PintuanOrders::find()->where([
            'status' => 1,
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if ($this->goods_id) {
            $query->andWhere(['goods_id' => $this->goods_id]);
        }

        $list = $query->with('goods')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['goods']['pic_url'] = \Yii::$app->serializer->decode($item['goods']['pic_url']);
            $item['goods']['attr_groups'] = \Yii::$app->serializer->decode($item['goods']['attr_groups']);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getPintuanDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var PintuanOrders $detail */
        $detail = PintuanOrders::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
        ])->with('goods.goodsWarehouse', 'orderRelation.user.userInfo', 'orderRelation.robot', 'orderRelation.order')
            ->one();

        if (!$detail) {
            throw new \Exception('拼团详情不存在');
        }

        $newDetail = ArrayHelper::toArray($detail);
        $newDetail['goods'] = $detail->goods ? ArrayHelper::toArray($detail->goods) : [];
        $newDetail['goods']['goodsWarehouse'] = isset($detail->goods->goodsWarehouse) ? ArrayHelper::toArray($detail->goods->goodsWarehouse) : [];
        $sign = 0;
        $newItemList = [];
        /** @var PintuanOrderRelation $item */
        foreach ($detail->orderRelation as $item) {
            if ($item->cancel_status == 0 && ($item->order && $item->order->is_pay == 1 || $item->order && $item->order->pay_type == 2) || $item->robot_id > 0) {
                $newItem = ArrayHelper::toArray($item);
                $newItem['user'] = $item->user ? ArrayHelper::toArray($item->user) : [];
                $newItem['order'] = $item->order ? ArrayHelper::toArray($item->order) : [];
                if ($item->user_id == \Yii::$app->user->id) {
                    $sign = 1;
                }
                if ($item->user) {
                    $newItem['user']['avatar'] = $item->user->userInfo->avatar;
                }
                // 机器人信息
                if ($item->robot) {
                    $newItem['user']['avatar'] = $item->robot->avatar;
                }
                $newItemList[] = $newItem;
            }
        }
        $newDetail['orderRelation'] = $newItemList;

        $pintuanGroups = PintuanGoodsGroups::find()
            ->where(['is_delete' => 0, 'goods_id' => $detail->goods->id])
            ->with(['attr.goodsAttr'])
            ->all();
        $groupMinPrice = 0;
        if (count($pintuanGroups) > 0) {
            /** @var PintuanGoodsGroups $group */
            foreach ($pintuanGroups as $group) {
                if ($group->people_num == $detail->people_num) {
                    foreach ($group->attr as $attr) {
                        $groupMinPrice = $groupMinPrice ? min($groupMinPrice, $attr->pintuan_price) : $attr->pintuan_price;
                    }
                }
            }
        }

        $pintuanTime = strtotime($detail->created_at) + $detail->pintuan_time * 60 * 60;
        $newDetail['surplus_people'] = (int)($detail->people_num - count($newDetail['orderRelation']));
        $newDetail['surplus_time'] = ($pintuanTime - time()) > 0 ? $pintuanTime - time() : 0;
        $newDetail['surplus_date_time'] = date('Y-m-d H:i:s', $pintuanTime);
        $newDetail['pintuan_sign'] = $sign;
        $newDetail['group_min_price'] = $groupMinPrice;
        $newDetail['group_economize_price'] = price_format($detail->goods->goodsWarehouse->original_price - $groupMinPrice);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newDetail,
            ]
        ];
    }
}
