<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\shopping\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Order;
use app\plugins\shopping\forms\common\CommonShopping;
use app\plugins\shopping\models\ShoppingBuys;
use yii\helpers\ArrayHelper;

class BuyOrderForm extends Model
{
    public $id;
    public $keyword;

    public function rules()
    {
        return [
            [['is_open', 'id'], 'integer'],
            [['keyword'], 'string']
        ];
    }

    /**
     * 待加入好物圈列表
     * @return array
     */
    public function getList()
    {
        $orderIds = ShoppingBuys::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
            // TODO 删除过的好物圈订单不能再次加入
            // 'b.is_delete' => 0
        ])->select('order_id');

        $list = Order::find()->alias('o')->where([
            'o.is_pay' => 1,
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
        ])
            ->andWhere(['not in', 'o.id', $orderIds])
            ->andWhere(['LIKE', 'o.order_no', $this->keyword])
            ->with(['detail.goods.goodsWarehouse'])
            ->page($pagination)
            ->orderBy('o.created_at DESC')
            ->asArray()
            ->all();


        foreach ($list as &$item) {
            foreach ($item['detail'] as $key => $detail) {
                $goods_info = json_decode($detail['goods_info'], true);
                $item['detail'][$key]['attr_list'] = $goods_info['attr_list'];
                $item['detail'][$key]['goods_info'] = $goods_info;
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 已加入好物圈的订单
     * @return array
     */
    public function getAddList()
    {
        $orderIds = ShoppingBuys::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
            'b.is_delete' => 0,
        ])->select('order_id');

//        dd($this->keyword);
        $list = Order::find()->alias('o')->where([
            'o.is_pay' => 1,
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
        ])
            ->andWhere(['o.id' => $orderIds])
            ->andWhere(['LIKE', 'o.order_no', $this->keyword])
            ->with(['detail.goods.goodsWarehouse'])
            ->page($pagination)
            ->orderBy('o.created_at DESC')
            ->asArray()
            ->all();


        /** @var Order $item */
        $orderModel = new Order();
        foreach ($list as &$item) {
            $item['order_status'] = $orderModel->orderStatusText($item);
            foreach ($item['detail'] as &$detail) {
                $goods_info = json_decode($detail['goods_info'], true);
                $detail['attr_list'] = $goods_info['attr_list'];
                $detail['goods_info'] = $goods_info;
            }
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function destroy()
    {
        try {
            $common = new CommonShopping();
            $res = $common->destroyBuyGood($this->id);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
