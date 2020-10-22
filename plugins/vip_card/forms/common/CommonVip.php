<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/6
 * Time: 14:25
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\vip_card\forms\common;

use app\events\OrderEvent;
use app\forms\mall\vip_card\VipCardForm;
use app\jobs\OrderCancelJob;
use app\models\GoodsCatRelation;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailVipCardInfo;
use app\plugins\vip_card\forms\api\IndexForm;
use app\plugins\vip_card\models\VipCard;
use app\plugins\vip_card\models\VipCardAppointGoods;
use app\plugins\vip_card\models\VipCardOrder;
use app\plugins\vip_card\models\VipCardUser;
use app\plugins\vip_card\Plugin;

/**
 * Class CommonVip
 * @package app\plugins\vip_card\forms\common
 * @property Mall $mall
 */
class CommonVip extends Model
{
    private static $instance;
    public $mall;

    public static function getCommon($mall = null)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        self::$instance->mall = $mall;
        return self::$instance;
    }

    private static $setting;
    private static $card;
    private static $vipUser;
    private static $user;
    private static $userInfo;
    private static $catsGoodsWarehouseIds;
    private static $appoints;

    /**
     * 单例模式获取主卡信息
     * @param int $id 主卡id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getMainCard($id = 0)
    {
        if (self::$card) {
            return self::$card;
        }
        self::$card = VipCard::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])
            ->keyword($id, ['id' => $id])
            ->limit(1)
            ->one();
        return self::$card;
    }

    /**
     * 是否未超级会员卡用户
     * @param $user
     * @return int[]
     */
    public function getUserInfo($user)
    {
        if (self::$userInfo) {
            $user = self::$userInfo;
        } else {
            $user = VipCardUser::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'user_id' => $user->id, 'is_delete' => 0])
                ->one();
            if ($user) {
                self::$userInfo = $user;
            } else {
                self::$userInfo = true;
            }
        }

        return [
            'is_vip_card_user' => isset($user->id) ? 1 : 0
        ];
    }

    private function getCats($cats)
    {
        if (!isset(self::$catsGoodsWarehouseIds)) {
            self::$catsGoodsWarehouseIds = GoodsCatRelation::find()->where([
                'cat_id' => $cats,
                'is_delete' => 0,
            ])->select('goods_warehouse_id')->column();
        }
        return self::$catsGoodsWarehouseIds;
    }

    public function getAppoints()
    {
        if (!isset(self::$appoints)) {
            self::$appoints = VipCardAppointGoods::find()
                ->select(['goods_id'])
                ->column();
        }
        return self::$appoints;
    }

    public function getAppoint($goods)
    {
        $my = 0;
        $discount = null;
        $isVipCardUser = 0;
        if (self::$user) {
            $user = self::$user;
        } else {
            $user = \Yii::$app->user->identity;
            self::$user = $user;
        }
        if ($user) {
            $res = $this->getUserInfo($user);
            if ($res['is_vip_card_user'] == 1) {
                $isVipCardUser = 1;
            }
        }
        if (self::$setting) {
            $setting = self::$setting;
        } else {
            $setting = (new Plugin())->getRules();
            self::$setting = $setting;
        }
        $appoint = $this->getAppoints();
        $isAppoint = in_array($goods['id'], $appoint) ? 1 : 0;
        if (empty(\Yii::$app->user->id)) {
            $vipCardUser = null;
        } else {
            if (isset(self::$vipUser)) {
                $vipCardUser = self::$vipUser;
            } else {
                $vipCardUser = VipCardUser::find()->where(['user_id' => \Yii::$app->user->id, 'is_delete' => 0])->one();
                if ($vipCardUser) {
                    self::$vipUser = $vipCardUser;
                } else {
                    self::$vipUser = [];
                }
            }
        }

        if (self::$card) {
            $card = self::$card;
        } else {
            $card = self::getMainCard();
            self::$card = $card;
        }
        if (!$card) {
            return [
                'discount' => $discount,
                'is_my_vip_card_goods' => $my,
                'is_vip_card_user' => $isVipCardUser,
            ];
        }

        //todo 逻辑优化
        if (!empty($vipCardUser)) {
            $type = json_decode($vipCardUser->image_type_info, true);
            if ($type['all'] == true) {
                if ($goods['sign'] == '') {
                    if ($isAppoint && $vipCardUser) {
                        $my = 1;
                        $discount = $vipCardUser->image_discount;
                    }
                } elseif (in_array($goods['sign'], $setting['rules'])) {
                    if ($isAppoint && $vipCardUser) {
                        $my = 1;
                        $discount = $vipCardUser->image_discount;
                    }
                }
            } else {
                if (
                    isset($type['goods']) && !empty($type['goods'])
                    && in_array($goods['goods_warehouse_id'], $type['goods'])
                ) {
                    if ($goods['sign'] == '') {
                        if ($isAppoint && $vipCardUser) {
                            $my = 1;
                            $discount = $vipCardUser->image_discount;
                        }
                    } elseif (in_array($goods['sign'], $setting['rules'])) {
                        if ($isAppoint && $vipCardUser) {
                            $my = 1;
                            $discount = $vipCardUser->image_discount;
                        }
                    }
                }

                if (isset($type['cats']) && !empty($type['cats'])) {
                    $goodsWarehouseIds = $this->getCats($type['cats']);
                    $isInCats = in_array($goods['goods_warehouse_id'], $goodsWarehouseIds) ? true : false;
                    if ($isInCats) {
                        if ($goods['sign'] == '') {
                            if ($isAppoint && $vipCardUser) {
                                $my = 1;
                                $discount = $vipCardUser->image_discount;
                            }
                        } elseif (in_array($goods['sign'], $setting['rules'])) {
                            if ($isAppoint && $vipCardUser) {
                                $my = 1;
                                $discount = $vipCardUser->image_discount;
                            }
                        }
                    }
                }
            }
        } else {
            $type = json_decode($card->type_info, true);

            if (isset($type) && is_array($type) && !empty($type)) {
                if ($type['all'] == true) {
                    if ($isAppoint) {
                        $discount = $card->discount;
                    }
                } else {
                    if (!empty($type['goods']) && in_array($goods['goods_warehouse_id'], $type['goods'])) {
                        if ($goods['sign'] == '') {
                            if ($isAppoint) {
                                $discount = $card->discount;
                            }
                        } elseif (in_array($goods['sign'], $setting['rules'])) {
                            if ($isAppoint) {
                                $discount = $card->discount;
                            }
                        }
                    }

                    if (isset($type['cats']) && !empty($type['cats'])) {
                        $goodsWarehouseIds = $this->getCats($type['cats']);
                        $isInCats = in_array($goods['goods_warehouse_id'], $goodsWarehouseIds) ? true : false;
                        if ($isInCats) {
                            if ($goods['sign'] == '') {
                                if ($isAppoint) {
                                    $discount = $card->discount;
                                }
                            } elseif (in_array($goods['sign'], $setting['rules'])) {
                                if ($isAppoint) {
                                    $discount = $card->discount;
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'discount' => $discount,
            'is_my_vip_card_goods' => $my,
            'is_vip_card_user' => $isVipCardUser,
        ];
    }

    public function getGoodsConfig()
    {
        return VipCardForm::check();
    }

    /**
     * @param $id
     * @param $token
     * @param array $orderDetailVipCardInfoData
     * @param bool $isOtherPluginSend 是否是插件赠送
     * @param string $sign 赠送的插件标识
     * @param int $payType 支付类型
     * @throws \Exception
     */
    public function generateOrders($id, $token, $orderDetailVipCardInfoData = [], $isOtherPluginSend = false, $sign = '', $payType = 1, $order_form = [])
    {
        $data = CommonVipCard::getCardDetail($id);
        if ($data['status'] == 1) {
            throw new \Exception('该会员卡已停售');
        }
        if ($data['num'] <= 0) {
            throw new \Exception('库存不足');
        }
        $vipCardSetting = (new CommonVipCardSetting())->getSetting();

        $order = new Order();
        $order->mall_id = \Yii::$app->mall->id;
        $order->user_id = \Yii::$app->user->id;
        $order->order_no = date('YmdHis') . rand(100000, 999999);
        $order->total_price = $isOtherPluginSend ? 0 : $data['price'];
        $order->total_pay_price = $isOtherPluginSend ? 0 : $data['price'];
        $order->express_original_price = 0;
        $order->express_price = 0;
        $order->total_goods_price = $isOtherPluginSend ? 0 : $data['price'];
        $order->total_goods_original_price = $isOtherPluginSend ? 0 : $data['price'];

        $order->member_discount_price = 0;
        $order->use_user_coupon_id = 0;
        $order->coupon_discount_price = 0;
        $order->use_integral_num = 0;
        $order->integral_deduction_price = 0;
        $order->remark = '';
        $order->order_form = \Yii::$app->serializer->encode($order_form);
        $order->words = '';
        $order->is_pay = $isOtherPluginSend ? 1 : 0;
        $order->pay_type = $isOtherPluginSend ? $payType : 0;
        $order->is_send = $isOtherPluginSend ? 1 : 0;
        $order->is_confirm = $isOtherPluginSend ? 1 : 0;
        $order->is_sale = $isOtherPluginSend ? 1 : 0;
        $order->support_pay_types = \Yii::$app->serializer->encode($vipCardSetting['payment_type']);
        $order->send_type = 3;

        $order->sign = $isOtherPluginSend ? $sign : 'vip_card';
        $order->token = $token;
        $order->status = 1;
        if (!$order->save()) {
            throw new \Exception($this->getErrorMsg($order));
        }

        $this->saveOrderDetail($order, $data);
        $this->saveVipOrder($order, $data);

        if (is_array($orderDetailVipCardInfoData)) {
            foreach ($orderDetailVipCardInfoData as &$item) {
                $orderDetailVipCardInfoModel = new OrderDetailVipCardInfo();
                $orderDetailVipCardInfoModel->vip_card_order_id = $order->id;
                $orderDetailVipCardInfoModel->order_detail_id = $item['order_detail_id'];
                $orderDetailVipCardInfoModel->order_detail_total_price = $item['order_detail_total_price'];
                $orderDetailVipCardInfoModel->save();
            }
        }

        if (!$isOtherPluginSend) {
            $event = new OrderEvent();
            $event->order = $order;
            $event->sender = $this;
            \Yii::$app->trigger(Order::EVENT_CREATED, $event);

            // 5分钟后取消订单
            $queueId = \Yii::$app->queue->delay(5 * 60)->push(new OrderCancelJob([
                 'orderId' => $order->id
             ]));
        }

        return $order->id;
    }

    /**
     * @param $order
     * @param $data
     * @param $order_form
     * @return bool
     * @throws \Exception
     */
    private function saveOrderDetail($order, $data)
    {
        $goods = (new IndexForm())->goods();
        $orderDetail = new OrderDetail();
        $orderDetail->order_id = $order->id;
        $orderDetail->goods_id = $goods->id;
        $orderDetail->num = 1;
        $orderDetail->unit_price = $data['price'];
        $orderDetail->total_original_price = $data['price'];
        $orderDetail->total_price = $data['price'];
        $orderDetail->member_discount_price = 0;
        $orderDetail->sign = 'vip_card';

        $attrGroups = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = [];
        foreach ($attrGroups as $attrGroup) {
            $arr['attr_group_id'] = $attrGroup['attr_group_id'];
            $arr['attr_group_name'] = $attrGroup['attr_group_name'];
            $arr['attr_id'] = $attrGroup['attr_list'][0]['attr_id'];
            $arr['attr_name'] = $attrGroup['attr_list'][0]['attr_name'];
            $attrList[] = $arr;
        }

        $shareData = $this->getShareMoney($orderDetail);

        $goodsInfo = [
            'attr_list' => $attrList,
            'goods_attr' => [
                'id' => $goods->attr[0]['id'],
                'goods_id' => $goods->id,
                'sign_id' => $goods->attr[0]['sign_id'],
                'stock' => $goods->attr[0]['stock'],
                'price' => $data['price'],
                'original_price' => $data['price'],
                'no' => $goods->attr[0]['no'],
                'weight' => $goods->attr[0]['weight'],
                'pic_url' => $data['main']['cover'],
                'share_commission_first' => $shareData['first'],
                'share_commission_second' => $shareData['second'],
                'share_commission_third' => $shareData['third'],
                'member_price' => 0,
                'integral_price' => 0,
                'use_integral' => 0,
                'discount' => [],// TODO 折扣为什么是数组
                'extra' => [],
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'name' => $data['main']['name'],
                'cover_pic' => $data['main']['cover'],
            ],
            'rules_data' => $data
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);

        if (!$orderDetail->save()) {
            throw new \Exception((new Model())->getErrorMsg($orderDetail));
        }

        return true;
    }

    /**
     * @param OrderDetail $orderDetail
     * @return array
     */
    private function getShareMoney($orderDetail)
    {
        $first = 0;
        $second = 0;
        $third = 0;

        $vipCardSetting = (new CommonVipCardSetting())->getSetting();
        if ($vipCardSetting['is_share']) {
            $firstValue = $vipCardSetting['share_commission_first'];
            if (!empty($firstValue) && is_numeric($firstValue)) {
                $first = $firstValue;
            }

            $secondValue = $vipCardSetting['share_commission_second'];
            if (!empty($secondValue) && is_numeric($secondValue)) {
                $second = $secondValue;
            }

            $thirdValue = $vipCardSetting['share_commission_third'];
            if (!empty($thirdValue) && is_numeric($thirdValue)) {
                $third = $thirdValue;
            }

            if ($vipCardSetting['share_type'] == 1) {
                $first = $first * $orderDetail->total_price / 100;
                $second = $second * $orderDetail->total_price / 100;
                $third = $third * $orderDetail->total_price / 100;
            } else {
                $first = $first * $orderDetail->num;
                $second = $second * $orderDetail->num;
                $third = $third * $orderDetail->num;
            }
        }

        return [
            'first' => $first,
            'second' => $second,
            'third' => $third
        ];
    }

    private function saveVipOrder($order, $data)
    {
        $cardOrder = new VipCardOrder();
        $cardOrder->mall_id = \Yii::$app->mall->id;
        $cardOrder->order_id = $order->id;
        $cardOrder->status = 0;
        $cardOrder->main_id = $data['main']['id'];
        $cardOrder->main_name = $data['main']['name'];
        $cardOrder->price = $data['price'];
        $cardOrder->detail_id = $data['id'];
        $cardOrder->user_id = $order->user_id;
        $cardOrder->detail_name = $data['name'];
        $cardOrder->expire = $data['expire_day'];
        $allSend['send_integral_num'] = $data['send_integral_num'];
        $allSend['send_balance'] = $data['send_balance'];
        $allSend['cards'] = $data['cards'];
        $allSend['coupons'] = $data['coupons'];
        $cardOrder->all_send = json_encode($allSend);
        if (!$cardOrder->save()) {
            throw new \Exception((new Model())->getErrorMsg($cardOrder));
        }
        return true;
    }
}
