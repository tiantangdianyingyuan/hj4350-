<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 16:27
 */


namespace app\plugins\exchange\forms\exchange\basic;

use app\core\payment\PaymentException;
use app\core\payment\PaymentOrder;
use app\models\PaymentOrderUnion;
use yii\base\Component;

class CopyPayment extends Component
{
    /**
     * @param $paymentOrders 支付订单数据，支持单个或多个订单
     * @param $user
     * @return int
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function createOrder($paymentOrders, $user)
    {
        if (!is_array($paymentOrders)) {
            if ($paymentOrders instanceof PaymentOrder) {
                $paymentOrders = [$paymentOrders];
            } else {
                throw new PaymentException("`$paymentOrders`不是有效的PaymentOrder对象。");
            }
        }

        if (!count($paymentOrders)) {
            throw new PaymentException("`$paymentOrders`不能为空。");
        }
        $orderNos = [];
        $amount = 0;
        $title = '';
        foreach ($paymentOrders as $paymentOrder) {
            $orderNos[] = $paymentOrder->orderNo;
            $amount = $amount + $paymentOrder->amount;
            $title = $title . str_replace(';', '', filter_emoji($paymentOrder->title)) . ';';
        }
        sort($orderNos);
        $orderNos[] = $amount;
        $unionOrderNo = 'HM' . mb_substr(md5(json_encode($orderNos)), 2);
        $title = mb_substr($title, 0, 32);
        $appVersion = \Yii::$app->getAppVersion();
        $paymentOrderUnion = new PaymentOrderUnion();
        $paymentOrderUnion->mall_id = $user->mall_id;
        $paymentOrderUnion->user_id = $user->id;
        $paymentOrderUnion->order_no = $unionOrderNo;
        $paymentOrderUnion->amount = $amount;
        $paymentOrderUnion->title = $title;
        if ($appVersion) {
            $paymentOrderUnion->app_version = $appVersion;
        }
        $supportPayTypes = $paymentOrder->supportPayTypes;
        $paymentOrderUnion->support_pay_types = $paymentOrderUnion->encodeSupportPayTypes($supportPayTypes);
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$paymentOrderUnion->save()) {
                throw new PaymentException();
            }
            foreach ($paymentOrders as $paymentOrder) {
                $model = new \app\models\PaymentOrder();
                $model->payment_order_union_id = $paymentOrderUnion->id;
                $model->order_no = $paymentOrder->orderNo;
                $model->amount = $paymentOrder->amount;
                $model->title = $paymentOrder->title;
                $model->notify_class = $paymentOrder->notifyClass;
                if (!$model->save()) {
                    throw new PaymentException();
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return $paymentOrderUnion->id;
    }


    /**
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyBalance($id)
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        if (intval($paymentOrderUnion->is_pay) === 1) {
            throw new PaymentException('订单已支付。');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 3;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 3;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }
}
