<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/9/6
 * Email: <657268722@qq.com>
 */

namespace app\plugins\advance\forms\api;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\advance\jobs\AdvanceRemindJob;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;

class AdvanceOrderPayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        $model = AdvanceOrder::findOne(['advance_no' => $paymentOrder->orderNo]);
        switch ($paymentOrder->payType) {
            case PaymentOrder::PAY_TYPE_BALANCE:
                $model->pay_type = 3;
                break;
            case PaymentOrder::PAY_TYPE_WECHAT:
                $model->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $model->pay_type = 1;
                break;
            default:
                break;
        }
        $model->is_pay = 1;
        $model->pay_time = date('Y-m-d H:i:s', time());
        if (!$model->save()) {
            \Yii::error('预售定金订单支付回调失败');
        }

        $goods = AdvanceGoods::findOne(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $model->goods_id]);
        if (!$goods) {
            return;
        }
        $second = strtotime($goods->end_prepayment_at) - time();
        $time = $second > 0 ? $second : 0;
        \Yii::$app->queue->delay($time)->push(new AdvanceRemindJob([
            'id' => $model->id,'goods' => $goods
        ]));
    }
}