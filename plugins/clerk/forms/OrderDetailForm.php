<?php

namespace app\plugins\clerk\forms;

use app\core\response\ApiCode;
use app\forms\api\goods\MallGoods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\plugins\mch\models\Mch;

class OrderDetailForm extends Model
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }


    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
            'is_delete' => 0,
        ])
            ->with('user')
            ->with('detail.goods.goodsWarehouse')
            ->with('refund')
            ->with('clerk')
            ->with('orderClerk')
            ->with('store')
            ->asArray()
            ->one();

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在',
            ];
        }
        $order['refund_info'] = [];
        if ($order['refund']) {
            $order['refund_info'] = $order['refund'][0];
            $order['refund'] = (new OrderRefund())->statusText_business($order['refund'][0]);
        }

        foreach ($order['detail'] as $key => $item) {
            $order['detail'][$key]['goods']['pic_url'] = \yii\helpers\BaseJson::decode($item['goods']['goodsWarehouse']['pic_url']);
            $order['detail'][$key]['goods']['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            $order['detail'][$key]['attr_list'] = json_decode($item['goods_info'], true)['attr_list'];
            $order['detail'][$key]['form_data'] = \yii\helpers\BaseJson::decode($item['form_data']);
            $order['detail'][$key]['goods_info'] = MallGoods::getGoodsData(OrderDetail::findOne($item['id']));
        }

        $order['order_form'] = json_decode($order['order_form'], true);

        //倒计时秒
        $order['auto_cancel'] = strtotime($order['auto_cancel_time']) - time();
        $order['auto_confirm'] = strtotime($order['auto_confirm_time']) - time();
        $order['auto_sales'] = strtotime($order['auto_sales_time']) - time();
        $mch = [];
        // 多商户
        if ($order['mch_id'] > 0) {
            $mch = Mch::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $order['mch_id']]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'order' => $order,
                'mch' => $mch,
            ]
        ];
    }
}
