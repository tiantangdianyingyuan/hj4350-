<?php

namespace app\plugins\exchange\handlers;

use app\handlers\HandlerBase;
use app\models\Order;
use app\models\User;
use app\plugins\exchange\forms\exchange\core\Reward;
use app\plugins\exchange\models\ExchangeCode;
use app\plugins\exchange\models\ExchangeRecordOrder;
use app\plugins\exchange\Plugin;

class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            if ($event->order->sign !== (new Plugin())->getName()) {
                return true;
            }
            if (!isset($event->sender->form_data['list'])) {
                return true;
            }
            $info = current($event->sender->form_data['list']);
            if (!isset($info['code']) || !isset($info['token'])) {
                \Yii::error('礼品卡订单');
                return true;
            }
            ///////////////////////////兑奖/////////////////////////////////////
            try {
                /** @var ExchangeCode $codeModel */
                $code = $info['code'];
                $token = $info['token'];

                $codeModel = ExchangeCode::find()->where(['code' => $code])->one();
                if (!$codeModel || !$token) {
                    throw new \Exception('警告');
                }

                //兑奖
                $user = User::findOne($event->order->user_id);
                $result_token = \Yii::$app->security->generateRandomString();
                $r = new Reward();
                $r->reward($codeModel, $user, $result_token, $token, ['origin' => 'alipay']);

                //取消 售后使用
                $order = new ExchangeRecordOrder();
                $order->mall_id = \Yii::$app->mall->id;
                $order->order_id = $event->order->id;
                $order->code_id = $codeModel->id;
                $order->token = $token;
                $order->user_id = $event->order->user_id;
                $order->save();
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }
}
