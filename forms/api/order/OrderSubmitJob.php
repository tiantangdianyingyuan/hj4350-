<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/17 10:56
 */


namespace app\forms\api\order;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\models\OrderVipCardInfo;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\mch\models\MchOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class OrderSubmitJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    /** @var array $data */
    public $form_data;

    /** @var string $token */
    public $token;

    public $sign;
    public $supportPayTypes;
    public $enableMemberPrice;
    public $enableFullReduce;
    public $enableCoupon;
    public $enableIntegral;
    public $enableOrderForm;
    public $enablePriceEnable;
    public $enableVipPrice;
    public $enableAddressEnable;
    public $status;
    public $appVersion;

    /** @var string $OrderSubmitFormClass */
    public $OrderSubmitFormClass;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $oldOrder = Order::findOne(['token' => $this->token, 'sign' => $this->sign, 'is_delete' => 0]);
            if ($oldOrder) {
                throw new \Exception('重复下单。');
            }
            /** @var OrderSubmitForm $form */
            $form = new $this->OrderSubmitFormClass();
            $form->form_data = $this->form_data;
            $form->setSign($this->sign)
                ->setEnableMemberPrice($this->enableMemberPrice)
                ->setEnableFullReduce($this->enableFullReduce)
                ->setEnableCoupon($this->enableCoupon)
                ->setEnableIntegral($this->enableIntegral)
                ->setEnablePriceEnable($this->enablePriceEnable)
                ->setEnableVipPrice($this->enableVipPrice)
                ->setEnableAddressEnable($this->enableAddressEnable)
                ->setEnableOrderForm($this->enableOrderForm);
            $form->setPluginData();
            $data = $form->getAllData();
            if (!$data['address_enable']) {
                throw new \Exception('当前收货地址不允许购买。');
            }
            if (!$data['price_enable']) {
                throw new \Exception('订单总价未达到起送要求。');
            }
            foreach ($data['mch_list'] as $mchItem) {
                $order = new Order();

                $order->mall_id = \Yii::$app->mall->id;
                $order->user_id = \Yii::$app->user->identity->getId();
                $order->mch_id = $mchItem['mch']['id'];

                $order->order_no = date('YmdHis') . rand(100000, 999999);

                $order->total_price = $mchItem['total_price'];
                $order->total_pay_price = $mchItem['total_price'];
                $order->express_original_price = $mchItem['express_price'];
                $order->express_price = $mchItem['express_price'];
                $order->total_goods_price = $mchItem['total_goods_price'];
                $order->total_goods_original_price = $mchItem['total_goods_original_price'];

                $order->member_discount_price = $mchItem['member_discount'];
                $order->full_reduce_price = $mchItem['full_reduce_discount'];
                $order->use_user_coupon_id = $mchItem['coupon']['use'] ? $mchItem['coupon']['user_coupon_id'] : 0;
                $order->coupon_discount_price = $mchItem['coupon']['coupon_discount'];
                $order->use_integral_num = $mchItem['integral']['use'] ? $mchItem['integral']['use_num'] : 0;
                $order->integral_deduction_price = $mchItem['integral']['use'] ?
                    $mchItem['integral']['deduction_price'] : 0;

                $order->name = $data['address'] ? $data['address']['name'] : '';
                $order->mobile = $data['address'] ? $data['address']['mobile'] : '';
                if ($mchItem['delivery']['send_type'] === 'express') {
                    $order->address = $data['address']['province']
                        . ' '
                        . $data['address']['city']
                        . ' '
                        . $data['address']['district']
                        . ' '
                        . $data['address']['detail'];
                } elseif ($mchItem['delivery']['send_type'] === 'city') {
                    $order->name = $mchItem['address']['name'];
                    $order->mobile = $mchItem['address']['mobile'];
                    $order->address = $mchItem['address']['location']
                        . ' '
                        . $mchItem['address']['detail'];
                }
                $order->remark = $mchItem['remark'];
                $order->order_form = $order->encodeOrderForm($mchItem['order_form_data']);
                $order->distance = isset($mchItem['form_data']['distance']) ? $mchItem['form_data']['distance'] : 0;//同城距离
                $order->words = '';

                $order->is_pay = 0;
                $order->pay_type = 0;
                $order->is_send = 0;
                $order->is_confirm = 0;
                $order->is_sale = 0;
                $order->support_pay_types = $order->encodeSupportPayTypes($this->supportPayTypes);

                if ($mchItem['delivery']['send_type'] === 'offline') {
                    if (empty($mchItem['store'])) {
                        throw new \Exception('请选择自提门店。');
                    }
                    $order->store_id = $mchItem['store']['id'];
                    $order->send_type = 1;
                } elseif ($mchItem['delivery']['send_type'] === 'city') {
                    $order->distance = $mchItem['distance'];
                    $order->location = $mchItem['address']['longitude'] . ',' . $mchItem['address']['latitude'];
                    $order->send_type = 2;
                    $order->store_id = 0;
                } elseif ($mchItem['delivery']['send_type'] === 'none') {
                    $order->send_type = 3;
                    $order->store_id = 0;
                } else {
                    $order->send_type = 0;
                    $order->store_id = 0;
                }

                $order->sign = $this->sign !== null ? $this->sign : '';
                $order->token = $this->token;
                $order->status = $this->status;

                if (!$order->save()) {
                    throw new \Exception((new Model())->getErrorMsg($order));
                }

                if ($mchItem['mch']['id'] > 0) {
                    $mchOrder = new MchOrder();
                    $mchOrder->order_id = $order->id;
                    $res = $mchOrder->save();
                    if (!$res) {
                        throw new \Exception('多商户订单创建失败');
                    }
                }

                $orderDetails = [];
                foreach ($mchItem['goods_list'] as $goodsItem) {
                    $form->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $orderDetails[] = $form->extraGoodsDetail($order, $goodsItem);
                }

                // 优惠券标记已使用
                if ($order->use_user_coupon_id) {
                    $userCoupon = UserCoupon::findOne($order->use_user_coupon_id);
                    $userCoupon->is_use = 1;
                    if ($userCoupon->update(true, ['is_use']) === false) {
                        throw new \Exception('优惠券状态更新失败。');
                    }
                }

                // 扣除积分
                if ($order->use_integral_num) {
                    $customDesc = \Yii::$app->serializer->encode($order->attributes);
                    if (!\Yii::$app->currency->integral->sub($order->use_integral_num, '下单积分抵扣', $customDesc)) {
                        throw new \Exception('积分操作失败。');
                    }
                }

                /**
                 * 开放额外的订单处理接口
                 */
                $form->extraOrder($order, $mchItem);

                // 购物车ID
                $cartIds = [];
                foreach ($mchItem['form_data']['goods_list'] as $goodsItem) {
                    $cartIds[] = $goodsItem['cart_id'];
                }

                // 下单同时开通超级会员卡时，生成超级会员卡的订单
                if (!empty($mchItem['vip_card_detail'])) {
                    /** @var \app\plugins\vip_card\models\VipCardDetail $vipCardDetail */
                    $vipCardDetail = $mchItem['vip_card_detail'];
                    $orderVipCardInfo = new OrderVipCardInfo();
                    $orderVipCardInfo->order_id = $order->id;
                    $orderVipCardInfo->vip_card_detail_id = $vipCardDetail->id;
                    $orderVipCardInfo->order_total_price = price_format($order->total_price - $mchItem['temp_vip_discount']);
                    $orderVipCardInfo->save();

                    $tempMchItem = (new \app\plugins\vip_card\Plugin())->vipDiscount($mchItem, true, $form);
                    $tempMchItem['goods_list'];
                    $orderDetailVipCardInfoData = [];
                    foreach ($tempMchItem['goods_list'] as $index => &$item) {
                        $orderDetailVipCardInfoData[] = [
                            'order_detail_id' => ($orderDetails[$index])->id,
                            'order_detail_total_price' => $item['total_price'],
                        ];
                    }


                    $vipCardJob = new \app\plugins\vip_card\jobs\OrderSubmitJob([
                        'mall' => $this->mall,
                        'user' => $this->user,
                        'id' => $vipCardDetail->id,
                        'token' => $order->token,
                        'orderDetailVipCardInfoData' => $orderDetailVipCardInfoData,
                    ]);
                    $vipCardJob->execute($queue);

                }

                $event = new OrderEvent();
                $event->order = $order;
                $event->sender = $this;
                $event->cartIds = $cartIds;
                $event->pluginData = [
                    'vip_discount' => $mchItem['vip_discount'] ?? null,
                    'flash_sale_discount' => $mchItem['flash_sale_discount'] ?? null,
                ];
                \Yii::$app->trigger(Order::EVENT_CREATED, $event);
            }

            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            \Yii::error($e);
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
            throw $e;
        }
    }
}
