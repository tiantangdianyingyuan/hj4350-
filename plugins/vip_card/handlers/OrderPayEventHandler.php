<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 15:49
 */

namespace app\plugins\vip_card\handlers;

use app\events\OrderEvent;
use app\forms\common\share\CommonShare;
use app\forms\common\template\TemplateSend;
use app\handlers\orderHandler\BaseOrderPayedHandler;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailVipCardInfo;
use app\models\OrderPayResult;
use app\plugins\vip_card\forms\common\AddShareOrder;
use app\plugins\vip_card\forms\common\CommonVipCard;
use app\plugins\vip_card\forms\common\CommonVipCardSetting;
use app\plugins\vip_card\models\VipCardDetail;
use app\plugins\vip_card\models\VipCardOrder;
use yii\db\Exception;

class OrderPayEventHandler extends BaseOrderPayedHandler
{
    public function handle()
    {
        self::execute();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        self::notice();
        self::pay();
    }

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        $userCouponList = $this->sendCoupon();
        $userCardList = $this->sendCard();
        $data = [
            'card_list' => $userCardList,
            'user_coupon_list' => $userCouponList,
            'send_data' => $this->getSendData(),
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    protected function notice()
    {
        \Yii::error('--vip_card notice--');
        $this->sendTemplate();
        $this->sendBuyPrompt();
        $this->setGoods();
        $this->receiptPrint('pay');
        return $this;
    }

    /**
     * 有点特殊，只能重写了
     */
    protected function sendTemplate()
    {
        try {
            $detail = $this->event->order->detail[0];
            $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);
            $data = [
                'keyword1' => [
                    'value' => $this->event->order->order_no,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $this->event->order->pay_time,
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $this->event->order->total_pay_price,
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $goodsInfo['goods_attr']['name'] ?? '超级会员卡',
                    'color' => '#333333',
                ]
            ];

            $template = new TemplateSend();
            $template->user = $this->event->order->user;
            $template->page = 'pages/order/index/index';
            $template->data = $data;
            $template->templateTpl = 'order_pay_tpl';
            $template->sendTemplate();
        } catch (\Exception $exception) {
            \Yii::error('订阅消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--vip_card pay--');
        $this->saveResult();
        $this->sendMail();
        $this->sendSms();
        $this->becomeJuniorByFirstPay();
        $this->becomeShare();
        self::addShareOrder();
        $this->updateOrderStatus();
        $this->updateVipOrderStatus();
        $this->updateVipCardNum();
        return $this;
    }

    public function becomeShare()
    {
        try {
            $setting = (new CommonVipCardSetting())->getSetting();
            if (!$setting['is_share'] || !$setting['is_buy_become_share']) {
                return $this;
            }
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->becomeShare(
                $this->event->order->user,
                [
                    'status' => 1,
                    'reason' => "购买超级会员卡自动成为分销商",
                    'apply_at' => mysql_timestamp(),
                ]
            );
        } catch (\Exception $exception) {
            \Yii::error('下单成为分销商(购买超级会员卡): ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return array
     * 向用户发送商品卡券
     */
    protected function sendCard()
    {
        try {
            $cardList = $this->sendCardAction();
        } catch (\Exception $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * @return array
     * 向用户发送优惠券（自动发送方案--订单支付成功发送优惠券）
     */
    protected function sendCoupon()
    {
        try {
            $userCouponList = $this->sendCouponAction();
        } catch (\Exception $exception) {
            \Yii::error('优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    private function sendCardAction()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $cardList = [];
        try {
            $dataArr = $this->getVipCardSend();
            CommonVipCard::sendCard(
                $dataArr,
                $this->event->order->mall_id,
                $this->event->order->user_id,
                $this->event->order->id,
                $this->event->order->detail[0]->id
            );
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('卡券发放失败');
            \Yii::error($exception);
        }

        return $cardList;
    }

    private function sendCouponAction()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $couponList = [];
        try {
            $dataArr = $this->getVipCardSend();
            CommonVipCard::sendCoupon($dataArr, $this->event->order->mall_id, $this->event->order->user_id);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('优惠券发放失败');
            \Yii::error($exception);
        }

        return $couponList;
    }

    private function getVipCardSend()
    {
        $detail = $this->event->order->detail[0];
        $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);


        return $goodsInfo['rules_data'];
    }

    public function updateOrderStatus()
    {
        \Yii::warning('超级会员卡订单状态更新开始');
        $order = $this->event->order;
        $order->is_sale = 1;
        $order->auto_sales_time = mysql_timestamp();
        $order->is_confirm = 1;
        $order->confirm_time = mysql_timestamp();
        $order->is_send = 1;
        $order->send_time = mysql_timestamp();
        $order->comment_time = mysql_timestamp();
        $res = $order->save();
        if (!$res) {
            \Yii::error('超级会员卡下单状态更新失败' . $this->getErrorMsg($order));
        }
        $this->updateOrderDetailVipCardTotalPrice();

        $event = new OrderEvent();
        $event->order = $order;
        \Yii::$app->trigger(Order::EVENT_SALES, $event);
    }

    public function updateVipOrderStatus()
    {
        $order = VipCardOrder::findOne(['order_id' => $this->event->order->id]);
        $order->status = 1;
        $res = $order->save();
        if (!$res) {
            \Yii::error('超级会员卡订单更新失败' . $this->getErrorMsg($order));
        }
    }

    public function getSendData()
    {
        $dataArr = $this->getVipCardSend();

        return [
            'send_balance' => $dataArr['send_balance'],
            'send_integral_num' => $dataArr['send_integral_num'],
        ];
    }

    public function updateVipCardNum()
    {
        $res = VipCardDetail::updateAllCounters(
            ['num' => -1],
            ['AND', ['id' => $this->getVipCardSend()['id']], ['>', 'num', 0]]
        );
        if (!$res) {
            throw new Exception('超级会员卡减少库存失败');
        }
    }

    public function addShareOrder()
    {
        try {
            (new AddShareOrder())->save($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('超级会员卡分销佣金记录失败：' . $exception->getMessage());
            \Yii::error($exception);
        }
        return $this;
    }

    /**
     * 更新订单超级会员价，如果有
     */
    public function updateOrderDetailVipCardTotalPrice()
    {
        $order = $this->event->order;
        $list = OrderDetailVipCardInfo::find()->where(
            [
                'vip_card_order_id' => $order->id,
            ]
        )->all();
        foreach ($list as $item) {
            $orderDetail = OrderDetail::findOne($item->order_detail_id);
            if (!$orderDetail) {
                continue;
            }
            $orderDetail->total_price = $item->order_detail_total_price;
            $orderDetail->save();
        }
    }
}
