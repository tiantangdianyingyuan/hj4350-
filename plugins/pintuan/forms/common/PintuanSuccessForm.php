<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common;


use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\forms\api\order\OrderException;
use app\forms\common\card\CommonSend;
use app\forms\common\CommonAppConfig;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\forms\common\template\TemplateSend;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetail;
use app\models\OrderPayResult;
use app\models\User;
use app\models\UserCard;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use app\plugins\pintuan\Plugin;
use app\plugins\wxapp\models\WxappTemplate;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class PintuanSuccessForm extends Model
{
    /** @var  PintuanOrders */
    public $pintuanOrder;

    public $orderCount;

    public function updateOrder()
    {
        try {
            // 查询拼团人数
            $orderIds = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $this->pintuanOrder->id,
                'robot_id' => 0
            ])->select('order_id');
            $orderCount = Order::find()->where([
                'id' => $orderIds,
                'mall_id' => \Yii::$app->mall->id
            ])->andWhere([
                'or',
                ['is_pay' => 1,],
                ['pay_type' => 2]
            ])->count();
            $robotCount = PintuanOrderRelation::find()->where([
                'pintuan_order_id' => $this->pintuanOrder->id,
            ])->andWhere(['>', 'robot_id', 0])->count();
            // 拼团组人数 要加上机器人数
            $orderCount += $robotCount;
            \Yii::warning('拼团机器人数：' . $robotCount);

            $this->orderCount = $orderCount;

            \Yii::warning('现拼团人数:' . $orderCount . '需拼团人数:' . $this->pintuanOrder->people_num);
            if ((int)$orderCount === (int)$this->pintuanOrder->people_num) {
                /** @var PintuanOrders $pintuanOrder */
                $pintuanOrder = PintuanOrders::find()->where([
                    'id' => $this->pintuanOrder->id
                ])->with('orderRelation.order')->one();
                $pintuanOrder->status = 2;
                $pintuanOrder->success_time = mysql_timestamp();
                $res = $pintuanOrder->save();
                if (!$res) {
                    \Yii::error($res);
                }

                // 将该拼团组订单 标记为已完成
                $orderIds = [];
                /** @var PintuanOrderRelation $item */
                foreach ($pintuanOrder->orderRelation as $item) {
                    // 判断是否为机器人
                    if ($item->robot_id == 0) {
                        $orderIds[] = $item->order_id;
                        // 拼团成功后 未支付的订单更新为取消状态
                        if ($item->order->is_pay == 0 && $item->order->cancel_status == 0 && $item->order->pay_type != 2) {
                            $item->order->cancel_status = 1;
                            $item->order->cancel_time = mysql_timestamp();
                            $res = $item->order->save();
                            if (!$res) {
                                \Yii::warning('拼团订单更新失败' . $this->getErrorMsg($item->order));
                            }
                        } else {
                            $this->sendTemplateMsg($item);
                            $this->sendSuccessCard($item->user_id, $item->order_id);
                            $this->receiptPrint($item->order);
                            $this->sendMail($item->order);
                            $this->sendSms($item->order);
                        }
                    }

                }
                $count = Order::updateAll([
                    'status' => 1
                ], [
                    'id' => $orderIds,
                ]);
                \Yii::warning($orderIds);
                \Yii::warning('订单状态更新成功' . $count);
            }
        } catch (\Exception $e) {
            \Yii::warning('拼团成功后,订单信息更新失败');
            \Yii::warning($e);
        }
    }

    /**
     * 拼团成功订阅消息
     * @param PintuanOrderRelation $item
     * @throws OrderException
     */
    private function sendTemplateMsg($item)
    {
        try {
            $user = User::findOne($item->user_id);
            if (!$user) {
                throw new OrderException('用户不存在！,拼团成功订阅消息发送失败');
            }

            $goodsName = '';
            /** @var OrderDetail $dItem */
            foreach ($item->order->detail as $dItem) {
                $goodsName .= $dItem->goods->getName();
            }

            $pintuanSuccessTemplate = new PintuanSuccessTemplate([
                'goodsName' => $goodsName,
                'order_no' => $item->order->order_no,
                'thing' => $item->order->user->nickname
            ]);

            $pintuanSuccessTemplate->page = 'plugins/pt/detail/detail?id=' . $item->pintuan_order_id;
            $pintuanSuccessTemplate->user = $user;
            $res = $pintuanSuccessTemplate->send();
        } catch (OrderException $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * // 拼团完成后发放
     * 向用户发送商品卡券
     * @param $userId
     * @param $orderId
     * @return array
     */
    private function sendSuccessCard($userId, $orderId)
    {
        try {
            $cardSendForm = new CommonSend();
            $cardSendForm->mall_id = \Yii::$app->mall->id;
            $cardSendForm->user_id = $userId;
            $cardSendForm->order_id = $orderId;
            /** @var UserCard[] $userCardList */
            $userCardList = $cardSendForm->save();
            $cardList = [];
            foreach ($userCardList as $userCard) {
                $cardList[] = $userCard->attributes;
            }
        } catch (OrderException $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }

        $data = [
            'card_list' => $cardList,
            'user_coupon_list' => [],
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $orderId;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();

        return $cardList;
    }

    /**
     * @param \app\models\Order $order
     * @return $this
     */
    private function receiptPrint($order)
    {
        try {
            $orderType = 'pay';
            $orderConfig = $this->getOrderConfig();
            if ($orderConfig->is_print != 1) {
                throw new \Exception($order->sign . '未开启小票打印');
            }
            $printer = new PrintOrder();
            $printer->print($order, $order->id, $orderType);
        } catch (PrintException $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     * 短信发送--新订单通知
     */
    private function sendSms($order)
    {
        try {
            $config = $this->getOrderConfig();
            if ($config->is_sms != 1) {
                throw new \Exception('未开启短信提醒');
            }
            $sms = new Sms();
            $smsConfig = CommonAppConfig::getSmsConfig();
            if ($smsConfig['status'] == 1 && $smsConfig['mobile_list']) {
                $sms->sendOrderMessage($smsConfig['mobile_list'], $order->order_no);
            }
        } catch (NoGatewayAvailableException $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 邮件发送--新订单通知
     */
    private function sendMail($order)
    {
        // 发送邮件
        try {
            $config = $this->getOrderConfig();
            if ($config->is_mail != 1) {
                throw new \Exception('未开启邮件提醒');
            }
            $mailer = new SendMail();
            $mailer->mall = Mall::findOne(\Yii::$app->mall->id);
            $mailer->order = $order;
            $mailer->orderPayMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送: ' . $exception->getMessage());
        }
        return $this;
    }

    private function getOrderConfig()
    {
        $plugin = new Plugin();
        $config = $plugin->getOrderConfig();

        return $config;
    }
}