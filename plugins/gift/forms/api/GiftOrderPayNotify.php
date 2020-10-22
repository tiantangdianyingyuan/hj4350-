<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 11:21
 */


namespace app\plugins\gift\forms\api;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\models\Mall;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\jobs\GiftTimeMsgJob;
use app\plugins\gift\jobs\GiftTimeOpenJob;
use app\plugins\gift\jobs\GiftTimeRefundJob;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSetting;

class GiftOrderPayNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order = GiftSendOrder::findOne([
                'order_no' => $paymentOrder->orderNo,
            ]);
            if (!$order) {
                throw new \Exception('礼物订单不存在');
            }
            $log_model = GiftLog::findOne($order->gift_id);
            $order->is_pay = 1;
            $log_model->is_pay = 1;
            switch ($paymentOrder->payType) {
                case PaymentOrder::PAY_TYPE_HUODAO:
                    $order->is_pay = 0;
                    $log_model->is_pay = 0;
                    $order->pay_type = 2;
                    break;
                case PaymentOrder::PAY_TYPE_BALANCE:
                    $order->pay_type = 3;
                    break;
                case PaymentOrder::PAY_TYPE_WECHAT:
                    $order->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_ALIPAY:
                    $order->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_BAIDU:
                    $order->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_TOUTIAO:
                    $order->pay_type = 1;
                    break;
                default:
                    break;
            }
            $order->pay_time = date('Y-m-d H:i:s', time());
            if (!$order->save()) {
                throw new \Exception('礼物订单保存错误：' . $order->errors[0]);
            }
            if (!$log_model->save()) {
                throw new \Exception('礼物记录保存错误：' . $log_model->errors[0]);
            }
            \Yii::$app->setMall(Mall::findOne($log_model->mall_id));
            $setting = GiftSetting::search();
            \Yii::error('礼物支付回调gift_id:' . $log_model->id . '-type:' . $log_model->type);
            //一系列队列
            $dataArr = [
                'mall' => \Yii::$app->mall,
                'gift_log_info' => $log_model,
            ];

            //自动退款操作，定时开奖在开奖后计算退款时间
            if ($setting['auto_refund'] > 0) {
                $class = new GiftTimeRefundJob($dataArr);
                if ($log_model->type == 'time_open') {
                    $qid = \Yii::$app->queue->delay(strtotime($log_model->open_time) - time() + 60)->push($class);
                } else {
                    $qid = \Yii::$app->queue->delay($setting['auto_refund'] * 86400 + 30)->push($class);
                }
                \Yii::error('礼物自动退款队列ID:' . $qid);
            }
            //礼物未领提醒
            if ($setting['auto_remind'] > 0 && $setting['auto_refund'] > 0) {
                $class = new GiftTimeMsgJob($dataArr);
                $qid = \Yii::$app->queue->delay($setting['auto_remind'] * 86400 + 30)->push($class);
                \Yii::error('礼物未领提醒队列ID:' . $qid);
            }
            //定时开奖，在送礼物时就放队列，等待到期开奖
            if ($log_model->type == 'time_open') {
                $class = new GiftTimeOpenJob($dataArr);
                $qid = \Yii::$app->queue->delay(abs(strtotime($log_model->open_time) - time()))->push($class);
                \Yii::error('定时开奖队列ID:' . $qid);
            }
            // 设置商城订单
            CommonGift::setOrder($order, $log_model);
            $t->commit();
            return true;
        } catch (\Exception $exception) {
            \Yii::error($exception);
            $t->rollBack();
            return false;
        }
    }


}
