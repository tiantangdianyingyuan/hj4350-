<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api;

use app\core\response\ApiCode;
use app\models\Delivery;
use app\models\Express;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetailExpress;
use yii\helpers\ArrayHelper;

class ExpressForm extends Model
{
    public $keyword;
    public $order_id;

    public function rules()
    {
        return [
            [['keyword'], 'string'],
            [['order_id'], 'integer'],
        ];
    }

    public function getExpressList()
    {
        $list = Express::getExpressList();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function getCustomer()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $express = Express::getExpressList();
        $query = Delivery::find()->select('customer_account')->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        $id = 0;
        foreach ($express as $v) {
            if (strstr($v['name'], $this->keyword) !== false) {
                $id = $v['id'];
                continue;
            }
        }
        $model = $query->andWhere(['express_id' => $id])->asArray()->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'customer_account' => $model['customer_account'] ?? '',
            ]
        ];
    }

    public function orderExpressList()
    {
        /** @var Order $order */
        $order = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id
        ])->with('detailExpress.expressRelation.orderDetail')->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在'
            ];
        }

        $newOrder = ArrayHelper::toArray($order);
        $newDetailExpress = [];
        /** @var OrderDetailExpress $detailExpress */
        foreach ($order->detailExpress as $detailExpress) {
            $newItem = ArrayHelper::toArray($detailExpress);
            $newExpressRelation = [];
            $goodsNum = 0;
            foreach ($detailExpress->expressRelation as $expressRelation) {
                $goodsNum = $goodsNum + $expressRelation->orderDetail->num;
                $newExpressRelationItem = ArrayHelper::toArray($expressRelation);
                $newExpressRelationItem['orderDetail'] = ArrayHelper::toArray($expressRelation->orderDetail);
                $newExpressRelationItem['orderDetail']['goods_info'] = \Yii::$app->serializer->decode($expressRelation->orderDetail->goods_info);
                $newExpressRelation[] = $newExpressRelationItem;
            }
            $newItem['expressRelation'] = $newExpressRelation;
            $newItem['goods_num'] = $goodsNum;
            $newDetailExpress[] = $newItem;
        }
        $newOrder['detailExpress'] = $newDetailExpress;

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'order' => $newOrder
            ]
        ];
    }
}
