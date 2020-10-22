<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;

use app\events\OrderEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\template\tplmsg\Tplmsg;
use app\jobs\ChangeShareOrderJob;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\models\ShareOrder;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use yii\db\Exception;

/**
 * @property User $user
 */
abstract class BaseOrderCanceledHandler extends BaseOrderHandler
{
    public $user;

    public function handle()
    {
        return $this->cancel();
    }

    protected function cancel()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var OrderEvent $event */
            $this->action();
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('订单取消完成事件：');
            \Yii::error($exception);
            throw $exception;
        }
    }

    protected function action()
    {
        $this->integralResume()->couponResume()->refund()->cardResume()
            ->shareResume()->sendTemplate()->updateGoodsInfo()->goodsAddStock($this->event->order);
    }

    /**
     * 用户积分恢复
     */
    protected function integralResume()
    {
        $user = User::findOne(['id' => $this->event->order->user_id]);
        if ($this->event->order->use_integral_num) {
            $desc = '商品订单取消，订单' . $this->event->order->order_no;
            \Yii::$app->currency->setUser($user)->integral
                ->refund((int)$this->event->order->use_integral_num, $desc);
        }
        return $this;
    }

    protected function couponResume()
    {
        // 优惠券恢复
        if ($this->event->order->use_user_coupon_id) {
            $userCoupon = UserCoupon::findOne(['id' => $this->event->order->use_user_coupon_id]);
            $userCoupon->is_use = 0;
            $userCoupon->save();
        }

        return $this;
    }

    protected function refund()
    {
        // 已付款就退款
        if ($this->event->order->is_pay == 1) {
            \Yii::$app->payment->refund($this->event->order->order_no, $this->event->order->total_pay_price);
        }
        return $this;
    }

    protected function cardResume()
    {
        /** @var UserCard[] $userCards */
        // 销毁发放的卡券
        $userCards = UserCard::find()->with('card')->where(['order_id' => $this->event->order->id])->all();
        foreach ($userCards as $userCard) {
            $userCard->is_delete = 1;
            $userCard->card->updateCount('add', 1);
            $res = $userCard->save();
            if (!$res) {
                \Yii::error('卡券销毁事件处理异常');
            }
        }
        return $this;
    }

    protected function shareResume()
    {
        ShareOrder::updateAll(['is_refund' => 1], ['order_id' => $this->event->order->id]);
        \Yii::$app->queue->delay(0)->push(new ChangeShareOrderJob([
            'mall' => \Yii::$app->mall,
            'order' => $this->event->order,
            'type' => 'sub',
            'before' => []
        ]));
        return $this;
    }

    protected function sendTemplate()
    {
        try {
            $template = new Tplmsg();
            $template->orderCancelMsg($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function updateGoodsInfo()
    {
        // 修改商品支付信息
        CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'sub');
        CommonGoods::getCommon()->setGoodsSales($this->event->order);

        return $this;
    }

    /**
     * @param Order $order
     * @throws Exception
     */
    protected function goodsAddStock($order)
    {
        /* @var OrderDetail[] $orderDetail */
        $orderDetail = $order->detail;
        $goodsAttrIdList = [];
        $goodsNum = [];
        foreach ($orderDetail as $item) {
            $goodsInfo = \Yii::$app->serializer->decode($item->goods_info);
            $goodsAttrIdList[] = $goodsInfo['goods_attr']['id'];
            $goodsNum[$goodsInfo['goods_attr']['id']] = $item->num;
        }
        $goodsAttrList = GoodsAttr::find()->where(['id' => $goodsAttrIdList])->all();
        /* @var GoodsAttr[] $goodsAttrList */
        foreach ($goodsAttrList as $goodsAttr) {
            $goodsAttr->updateStock($goodsNum[$goodsAttr->id], 'add');
        }

        return $this;
    }
}
