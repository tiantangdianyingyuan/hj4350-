<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\common\v2;

use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\forms\api\order\OrderException;
use app\forms\common\card\CommonSend;
use app\forms\common\CommonAppConfig;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\coupon\CommonCouponGoodsSend;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
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
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class PintuanSuccessForm extends Model
{
    /** @var  PintuanOrders */
    public $pintuanOrder;

    public $orderCount;

    public function updateOrder()
    {
        try {
            \Yii::$app->setMall(Mall::findOne($this->pintuanOrder->mall_id));
            // 查询拼团人数
            $orderIds = PintuanOrderRelation::find()->where(['pintuan_order_id' => $this->pintuanOrder->id, 'robot_id' => 0])->select('order_id');
            $orderCount = Order::find()->where(['id' => $orderIds, 'mall_id' => \Yii::$app->mall->id])
                ->andWhere([
                    'or',
                    ['is_pay' => 1],
                    ['pay_type' => 2],
                ])->count();

            $robotCount = PintuanOrderRelation::find()->where(['pintuan_order_id' => $this->pintuanOrder->id])->andWhere(['>', 'robot_id', 0])->count();
            // 拼团组人数 要加上机器人数
            $orderCount += $robotCount;
            $this->orderCount = $orderCount;

            \Yii::warning('拼团机器人数：' . $robotCount);
            \Yii::warning('拼团订单ID:' . $this->pintuanOrder->id . ' 现拼团人数:' . $orderCount . '需拼团人数:' . $this->pintuanOrder->people_num);
            // TODO 上面拼团人数要返回 下面if应判断是否成团 成团就不再往下执行
            if ((int) $orderCount >= (int) $this->pintuanOrder->people_num && $this->pintuanOrder->status != 2) {
                /** @var PintuanOrders $pintuanOrder */
                $pintuanOrder = PintuanOrders::find()->where(['id' => $this->pintuanOrder->id])->with('orderRelation.order', 'goods')->one();
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
                        if ($item->order->is_pay == 1 || $item->order->pay_type == 2 && $item->order->cancel_status != 1) {
                            \Yii::warning('拼团订单更新1，ID:' . $item->order->id);
                            // 拼团成功自动收货
                            CommonEcard::getCommon()->autoSend($item->order);
                            // 拼团成功才算入销量
                            \app\forms\common\goods\CommonGoods::getCommon()->setGoodsSales($item->order);
                            $this->sendTemplateMsg($item);
                            $this->sendSuccess($item->user_id, $item->order_id);
                            $this->receiptPrint($item->order);
                            $this->sendMail($item->order);
                            $this->sendSms($item->order);
                        } else if ($item->order->is_pay == 0 && $item->order->cancel_status == 0 && $item->order->pay_type != 2) {
                            \Yii::warning('拼团订单更新2，ID:' . $item->order->id);
                            // 拼团成功后 未支付的订单更新为取消状态
                            $item->order->cancel_status = 1;
                            $item->order->cancel_time = mysql_timestamp();
                            $res = $item->order->save();
                            if (!$res) {
                                \Yii::warning('拼团订单更新失败' . $this->getErrorMsg($item->order));
                            }
                        } else {
                            \Yii::warning('拼团订单更新3，ID:' . $item->order->id);
                            \Yii::warning($item->order);
                        }

                    }
                }
                $count = Order::updateAll(['status' => 1], ['id' => $orderIds]);
                \Yii::warning($orderIds);
                \Yii::warning('拼团订单状态更新成功,更新订单数:' . $count);
                $this->sendMpTemplate($pintuanOrder);
            }
        } catch (\Exception $e) {
            \Yii::warning('更新拼团成功状态失败,错误信息：' . $e->getMessage());
        }
    }

    /**
     * 拼团成功模板消息
     * @param PintuanOrderRelation $item
     * @throws OrderException
     */
    private function sendTemplateMsg($item)
    {
        try {
            $user = User::findOne($item->user_id);
            if (!$user) {
                throw new OrderException('用户不存在！,拼团成功模板消息发送失败');
            }

            $goodsName = '';
            /** @var OrderDetail $dItem */
            foreach ($item->order->detail as $dItem) {
                $goodsName .= $dItem->goods->getName();
            }

            $pintuanSuccessTemplate = new PintuanSuccessTemplate([
                'goodsName' => $goodsName,
                'order_no' => $item->order->order_no,
                'thing' => $item->order->user->nickname,
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
     * 向用户发送商品卡券、优惠券
     * @param $userId
     * @param $orderId
     * @return array
     * @throws \yii\db\Exception
     */
    private function sendSuccess($userId, $orderId)
    {
        $cardList = $this->sendCard($userId, $orderId);
        $userCouponList = $this->sendCouponByGoods($userId, $orderId);

        $data = [
            'card_list' => $cardList,
            'user_coupon_list' => $userCouponList,
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $orderId;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();

        return $cardList;
    }

    /**
     * 发卡券
     * @param $userId
     * @param $orderId
     * @return array
     * @throws \yii\db\Exception
     */
    private function sendCard($userId, $orderId)
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
            \Yii::warning('拼团成功卡券发放数据');
            \Yii::warning($cardList);
        } catch (OrderException $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * 发优惠券
     * @param $userId
     * @param $orderId
     * @return array
     */
    private function sendCouponByGoods($userId, $orderId)
    {
        try {
            $couponSendForm = new CommonCouponGoodsSend();
            $couponSendForm->user = User::findOne($userId);
            $couponSendForm->mall = \Yii::$app->mall;
            $couponSendForm->order_id = $orderId;
            $userCouponList = $couponSendForm->send();
            \Yii::warning('拼团---购买商品赠送优惠券发放数据');
            \Yii::warning($userCouponList);
        } catch (\Exception $exception) {
            \Yii::error('拼团赠送优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
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
            $mailer->mall = \Yii::$app->mall;
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

    /**
     * @return $this
     * 通过公众号向商家发送公众号消息
     */
    private function sendMpTemplate($pintuanOrder)
    {
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'newOrderTpl';
            $tplMsg->params = [
                'sign' => (new Plugin())->getName(),
                'goods' => $pintuanOrder->goods->name,
                'time' => date('Y-m-d H:i:s'),
                'user' => $pintuanOrder->people_num . '人团',
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }
}
