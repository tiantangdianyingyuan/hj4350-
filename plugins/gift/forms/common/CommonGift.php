<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\common;

use app\forms\common\CommonAppConfig;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Model;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\gift\events\OrderEvent;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\gift\models\GiftSendOrderDetail;
use app\plugins\gift\models\GiftSetting;
use app\plugins\gift\models\GiftUserOrder;
use app\plugins\gift\Plugin;
use Overtrue\EasySms\Message;
use yii\helpers\ArrayHelper;

class CommonGift extends Model
{
    public static function getSetting()
    {
        $setting = \app\forms\common\CommonOption::get('gift_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = GiftSetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }

        $default = self::getDefault();
        if ($setting) {
            $setting['background'] = \yii\helpers\Json::decode($setting['background']) ?: $default['background'];
            $setting['payment_type'] = \yii\helpers\Json::decode($setting['payment_type']) ?: $default['payment_type'];
            $setting['send_type'] = \yii\helpers\Json::decode($setting['send_type']) ?: $default['send_type'];
            $setting['type'] = \yii\helpers\Json::decode($setting['type']) ?: $default['type'];
            $setting['theme'] = \yii\helpers\Json::decode($setting['theme']) ?: $default['theme'];
            $setting['poster'] = \yii\helpers\Json::decode($setting['poster']) ?: $default['poster'];

            $diffSetting = array_diff_key($default, $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = $default;
        }

        $setting['default'] = $default;
        return $setting;
    }

    private static function getDefault()
    {
        return [
            'is_territorial_limitation' => 0,
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'title' => '送礼物',
            'type' => ['direct_open', 'num_open', 'time_open'],
            'auto_refund' => 2,
            'auto_remind' => 1,
            'bless_word' => '送你好礼，万事如意',
            'ask_gift' => '我发现了一个不错的礼物，快来看看吧！',
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'payment_type' => ['online_pay'],
            'poster' => CommonOption::getPosterDefault(),
            'theme' => [
                'id' => 1,
                'active' => true,
                'text' => '流金色',
                'pic_url' => 'streamer-gold'
            ],
            'send_type' => ['express'],
            'background' => [
                'bg_pic' => PHP_SAPI != 'cli' ? \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/plugins/pop-ups.png': '',
                'top' => 153,
                'left' => 67,
                'color' => 'rgb(255, 255, 255)'
            ],
            'explain' => '<p>
    买家在商城挑选商品，下单付款，将商品作为礼物发给亲朋好友，好友领取礼物后填写收货地址，商家将礼物快递配送给好友，即完成一次送礼过程。
</p>
<p>
    <br/>
</p>
<p>
    玩法介绍<br/>
</p>
<p>
    <br/>
</p>
<p>
    直接送礼
</p>
<p>
    <span style="color: rgb(165, 165, 165);">送礼人挑选礼物，下单付款并分享给好友，收礼人即可领取，收礼人填写收货地址后，只需静候礼物快递上门定时开奖。<br/></span>
</p>
<p>
    <span style="color: rgb(165, 165, 165);">送礼人需要设置开奖时间，开奖时间到时，系统自动从参与用户中抽取中奖用户。</span><br/>
</p>
<p>
    <br/>
</p>
<p>
    满人开奖<br/>
</p>
<p>
    <span style="color: rgb(165, 165, 165);">送礼人需要设置开奖的人数要求，参与人数到达设置的要求时，系统自动从参与用户中抽取中奖用户。</span>
</p>
<p>
    <br/>
</p>
<p>
    一人领取
</p>
<p>
    <span style="color: rgb(165, 165, 165);">送礼人下单的礼物，只限一名用户领取。</span><br/>
</p>
<p>
    <br/>
</p>
<p>
    多人领取<br/>
</p>
<p>
    <span style="color: rgb(165, 165, 165);">送礼人购买多件商品后，可多人领取礼物，每人限制一份。</span>
</p>',
            'is_full_reduce' => 1
        ];
    }

    /**
     * @param $gift_id
     * @param $gift_log
     * @param $type
     * @param null $user_id 直接送礼，必填
     * @return bool
     * @throws \Exception
     */
    public static function openGift($gift_id, $gift_log, $type, $user_id = null)
    {
        \Yii::error('礼物开奖开始——————————————————————————————————');

        /* @var GiftLog $gift_log */
        //查询可送礼物商品
        $gift_order = GiftSendOrder::find()->where([
            'gift_id' => $gift_id,
            'is_pay' => 1,
            'is_refund' => 0,
            'is_confirm' => 0,
            'is_delete' => 0,
        ])->with('detail')->asArray()->all();
        if (empty($gift_order)) {
            throw new \Exception('礼物已被领光');
        }

        // 获取买礼物的商城订单及订单详情
        /* @var Order $order */
        $orderList = Order::find()->where(['order_no' => array_column($gift_order, 'order_no')])
            ->with('detail')->all();
        $buyOrderDetail = [];
        foreach ($orderList as $order) {
            foreach ($order->detail as $orderDetail) {
                $goodsInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
                $buyOrderDetail[$goodsInfo['goods_attr']['id']][] = $orderDetail->id;
            }
        }

        //模版消息的一些参数
        $data['title'] = ($type == 'num_open') ? '满人开奖' : (($type == 'time_open') ? '定时开奖' : '开奖');
        if (count($gift_order) > 1 || count($gift_order[0]['detail']) > 0) {
            $data['name'] = '多商品大礼包';
        } else {
            $goods = Goods::find()->where(['id' => $gift_order[0]['detail'][0]['goods_id']])
                ->with('goodsWarehouse')->one();
            $data['name'] = $goods->getName();
        }

        //非直接送礼
        $user_order_arr = [];
        if ($type != 'direct_open') {
            $user_order_list = GiftUserOrder::find()
                ->where(['gift_id' => $gift_id, 'is_turn' => 0, 'is_receive' => 0, 'is_delete' => 0, 'is_win' => 0])
                ->asArray()->all();
            if (empty($user_order_list)) {
                throw new \Exception('无参与抽奖用户');
            }
            //生成用户池
            foreach ($user_order_list as $item) {
                $user_order_arr[] = [
                    'id' => $item['id'],
                    'user_id' => $item['user_id']
                ];
            }
        } else {
            $user_order_list = GiftUserOrder::find()
                ->where([
                    'user_id' => $user_id, 'gift_id' => $gift_id, 'is_turn' => 0, 'is_receive' => 0, 'is_delete' => 0,
                    'is_win' => 0
                ])
                ->asArray()->one();
            $user_order_arr[] = [
                'id' => $user_order_list['id'],
                'user_id' => $user_order_list['user_id']
            ];
        }
        //领取形式
        if ($gift_log->open_type == 1) {
            //礼物商品放入奖池——随机领取
            $gift_arr = [];
            foreach ($gift_order as $detail_list) {
                foreach ($detail_list['detail'] as $detail) {
                    $remnant_num = $detail['num'] - $detail['receive_num'];//剩余未领取礼物数
                    if ($remnant_num < 0) {
                        throw new \Exception(
                            '礼物领取超量' .
                            '-gift_id:' . $gift_id .
                            '-order_id:' . $detail_list['id'] .
                            '-detail_id:' . $detail['id']
                        );
                    }
                    for ($i = 0; $i < $remnant_num; $i++) {
                        $gift_arr[] = [
                            'detail_id' => $detail['id'],
                            'goods_id' => $detail['goods_id'],
                            'goods_attr_id' => $detail['goods_attr_id'],
                            'buy_order_detail_id' => $buyOrderDetail[$detail['goods_attr_id']][$detail['receive_num']]
                        ];
                    }
                }
            }

            //取礼物数或参与人数少的一方做循环
            $b = count($gift_arr) > count($user_order_arr) ? count($user_order_arr) : count($gift_arr);
            for ($a = 0; $a < $b; $a++) {
                //随机获得领取礼物
                \Yii::error('执行随机获得领取礼物');
                $goods_rand_num = rand(0, count($gift_arr) - 1);
                $user_order_num = rand(0, count($user_order_arr) - 1);

                $gift_order_model = new GiftOrder();
                $gift_order_model->gift_id = $gift_id;
                $gift_order_model->mall_id = $gift_log->mall_id;
                $gift_order_model->order_no = Order::getOrderNo('GF');
                $gift_order_model->goods_id = $gift_arr[$goods_rand_num]['goods_id'];//随机获取的礼物ID
                $gift_order_model->goods_attr_id = $gift_arr[$goods_rand_num]['goods_attr_id'];//随机获取的礼物规格ID
                $gift_order_model->num = 1;
                $gift_order_model->type = $type;
                $gift_order_model->user_order_id = $user_order_arr[$user_order_num]['id'];
                $gift_order_model->buy_order_detail_id = $gift_arr[$goods_rand_num]['buy_order_detail_id'];
                if (!$gift_order_model->save()) {
                    throw new \Exception($gift_order_model->errors[0]);
                }
                //记录中奖数
                $detail_info = GiftSendOrderDetail::findOne([
                    'id' => $gift_arr[$goods_rand_num]['detail_id'],
                    'goods_id' => $gift_arr[$goods_rand_num]['goods_id'],
                    'goods_attr_id' => $gift_arr[$goods_rand_num]['goods_attr_id']
                ]);
                if (empty($detail_info)) {
                    throw new \Exception('礼物订单信息有误');
                }
                $detail_info->receive_num += 1;
                if (!$detail_info->save()) {
                    throw new \Exception($detail_info->errors[0]);
                }
                //中奖信息返回保存抽奖表
                $user_order_model = GiftUserOrder::findOne([
                    'user_id' => $user_order_arr[$user_order_num]['user_id'],
                    'gift_id' => $gift_id,
                    'mall_id' => $gift_log->mall_id,
                    'is_delete' => 0,
                    'is_turn' => 0,
                    'is_win' => 0]);
                if (empty($user_order_model)) {
                    throw new \Exception('参与信息有误');
                }
                $user_order_model->is_win = 1;
                if (!$user_order_model->save()) {
                    throw new \Exception($user_order_model->errors[0]);
                }
                //在未序列化前，发送模版消息
                $data['user'] = User::find()->where(['id' => $user_order_arr[$user_order_num]['user_id']])
                    ->with('userInfo')->one();
                $data['remark'] = '恭喜您，中奖了！';
                $msg = self::sendMsg($type, $data);
                \Yii::error('随机开奖模版消息状态' . json_encode($msg));
                //去除已中奖用户和礼物，并重新序列化
                unset($gift_arr[$goods_rand_num]);
                unset($user_order_arr[$user_order_num]);
                $gift_arr = array_values($gift_arr);
                $user_order_arr = array_values($user_order_arr);
            }
            //礼物领光后更改状态，或非直接送开奖后直接算已完成送礼
            if (empty($gift_arr) || $type != 'direct_open') {
                $gift_log->is_confirm = 1;
                if (!$gift_log->save()) {
                    throw new \Exception($gift_log->errors[0]);
                }
                //更改礼物订单状态
                GiftSendOrder::updateAll(['is_confirm' => 1], ['gift_id' => $gift_id]);
            }
        } elseif ($gift_log->open_type == 0) {
            if (count($user_order_arr) > 0) {
                //一人领取全部
                \Yii::error('执行一人领取全部礼物');
                $order_no = Order::getOrderNo('GF');
                $user_order_num = rand(0, count($user_order_arr) - 1);
                foreach ($gift_order as $detail_list) {
                    foreach ($detail_list['detail'] as $detail) {
                        //领取所有礼物
                        $gift_order_model = new GiftOrder();
                        $gift_order_model->gift_id = $gift_id;
                        $gift_order_model->mall_id = $gift_log->mall_id;
                        $gift_order_model->order_no = $order_no;
                        $gift_order_model->goods_id = $detail['goods_id'];
                        $gift_order_model->goods_attr_id = $detail['goods_attr_id'];
                        $gift_order_model->num = $detail['num'];
                        $gift_order_model->type = $type;
                        $gift_order_model->user_order_id = $user_order_arr[$user_order_num]['id'];
                        $gift_order_model->buy_order_detail_id = $buyOrderDetail[$detail['goods_attr_id']][0];
                        if (!$gift_order_model->save()) {
                            throw new \Exception($gift_order_model->errors[0]);
                        }
                        //记录中奖数
                        $detail_info = GiftSendOrderDetail::findOne(['id' => $detail['id'], 'goods_id' => $detail['goods_id'], 'goods_attr_id' => $detail['goods_attr_id']]);
                        if (empty($detail_info)) {
                            throw new \Exception($detail_info->errors[0]);
                        }
                        $detail_info->receive_num += $detail['num'];
                        if (!$detail_info->save()) {
                            throw new \Exception($detail_info->errors[0]);
                        }
                    }
                }

                //中奖信息返回保存抽奖表
                $user_order_model = GiftUserOrder::findOne([
                    'user_id' => $user_order_arr[$user_order_num]['user_id'],
                    'gift_id' => $gift_id,
                    'mall_id' => $gift_log->mall_id,
                    'is_delete' => 0,
                    'is_turn' => 0,
                    'is_win' => 0]);
                if (empty($user_order_model)) {
                    throw new \Exception($user_order_model->errors[0]);
                }
                $user_order_model->is_win = 1;
                if (!$user_order_model->save()) {
                    throw new \Exception($user_order_model->errors[0]);
                }

                //在未序列化前，发送模版消息
                $data['user'] = User::find()->where(['id' => $user_order_arr[$user_order_num]['user_id']])->with('userInfo')->one();
                $data['remark'] = '恭喜您，中奖了！';
                $msg = self::sendMsg($type, $data);
                \Yii::error('一人拿开奖模版消息状态' . json_encode($msg));
                //去除已中奖用户和礼物，并重新序列化
                unset($user_order_arr[$user_order_num]);
                $user_order_arr = array_values($user_order_arr);
            }
            //更改领取状态值
            $gift_log->is_confirm = 1;
            if (!$gift_log->save()) {
                throw new \Exception($gift_log->errors[0]);
            }
            GiftSendOrder::updateAll(['is_confirm' => 1], ['gift_id' => $gift_id]);
        } else {
            throw new \Exception('开奖方式错误');
        }
        //给未中奖用户发送模版消息
        $user_ids = [];
        foreach ($user_order_arr as $item) {
            $user_ids[] = $item['user_id'];
        }
        if (!empty($user_ids)) {
            $users = User::find()->where(['in', 'id', $user_ids])->with('userInfo')->asArray()->all();
            foreach ($users as $value) {
                $data['user'] = $value;
                $data['remark'] = '很遗憾，未中奖！';
                $msg = self::sendMsg($type, $data);
                \Yii::error('未中奖模版消息状态' . json_encode($msg));
            }
        }

        return true;
    }

    private static function sendMsg($type, $data)
    {
        if ($type == 'direct_open') {
            return true;
        }
        return (new GiftConvertTemplate($data))->send();
    }

    /**
     * @param $gift_log
     * @return bool
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public static function refundGift($gift_log)
    {
        \Yii::error('礼物自动退款开始————————————————————————————————');
        /* @var GiftLog $gift_log */
        $order = GiftSendOrder::find()
            ->andWhere(['gift_id' => $gift_log->id, 'is_pay' => 1, 'is_delete' => 0, 'is_refund' => 0])
            ->with('detail')
            ->asArray()->all();
        if (empty($order)) {
            throw new \Exception('礼物退款——礼物信息有误');
        }
        foreach ($order as $value) {
            $refund_price = 0;
            foreach ($value['detail'] as $item) {
                $price = bcmul(bcdiv($item['total_price'], $item['num']), bcsub($item['num'], $item['receive_num']));
                \Yii::error('金额：' . $price);
                if ($price < 0) {
                    throw new \Exception('礼物退款——退款金额发生错误');
                }
                $refund_price += $price;
                $detail_model = GiftSendOrderDetail::findOne($item['id']);
                $detail_model->refund_price = $price;
                $detail_model->is_refund = 1;
                if (!$detail_model->save()) {
                    throw new \Exception($detail_model->errors[0]);
                }
                //回退库存
                $goods_info = \Yii::$app->serializer->decode($detail_model->goods_info);
                $attr = GoodsAttr::findOne($goods_info['goods_attr']['id']);
                $attr->updateStock(bcsub($item['num'], $item['receive_num']), 'add');
            }
            $order_model = GiftSendOrder::findOne($value['id']);
            if ($refund_price > 0) {
                $re = \Yii::$app->payment->refund($value['order_no'], $refund_price);
                if ($re) {
                    $order_model->is_refund = 1;
                    if (!$order_model->save()) {
                        throw new \Exception($order_model->errors[0]);
                    }
                    $user = User::find()->where(['id' => $value['user_id']])->with('userInfo')->one();
                    self::sendRefundMsg(['order_no' => $value['order_no'], 'name' => '礼物', 'user' => $user], $refund_price, '礼物到期自动退款');
                    \Yii::error('礼物自动退款gift_order_id:' . $value['id'] . '-退款金额:' . $refund_price);
                }
            }
        }

        if (!self::giftOver($gift_log->id)) {
            \Yii::error('礼物订单结束失败');
        }
        return true;
    }

    /**
     * @param array|string $mobiles
     * @param string $type gift|gift_lottery
     */
    public static function sendSms($mobiles, $type)
    {
        try {
            if (!is_array($mobiles)) {
                $mobiles = [$mobiles];
            }
            $smsConfig = CommonAppConfig::getSmsConfig();
            if ($smsConfig['status'] == 1 && isset($smsConfig[$type]) && $smsConfig[$type]['template_id']) {
                $message = new Message([
                    'template' => $smsConfig[$type]['template_id'],
                    'data' => []
                ]);
                foreach ($mobiles as $mobile) {
                    \Yii::$app->sms->module('mall')->send($mobile, $message);
                }
            }
            return true;
        } catch (\Exception $exception) {
            \Yii::error('=====礼物短信发送=====');
            \Yii::error($exception);
        }
    }

    /**
     * @param $gift_id
     * @return bool
     */
    public static function giftOver($gift_id)
    {
        $data = GiftOrder::find()->alias('go')
            ->leftJoin(['guo' => GiftUserOrder::tableName()], 'guo.id = go.user_order_id')
            ->with(['order'])
            ->andWhere(['guo.gift_id' => $gift_id, 'go.is_delete' => 0])
            ->select(['go.order_id', 'go.is_refund'])
            ->asArray()
            ->all();
        if (empty($data)) {
            \Yii::error('礼物中奖订单未找到gift_id:' . $gift_id);
//            return false;不能返回false
        }

        \Yii::error('礼物结束流程开始');
        $gift_order = GiftSendOrder::findOne(['gift_id' => $gift_id, 'is_delete' => 0]);

        if (!empty($data)) {
            $is_sale = true;
            $is_refund = true;
            $is_order = false;
            foreach ($data as $item) {
                if (!empty($item['order']) && $item['order']['is_sale'] == 0) {
                    $is_sale = false;
                }
                if (empty($item['order']) && $item['is_refund'] == 0) {
                    $is_refund = false;
                }
                if (!empty($item['order'])) {
                    $is_order = true;
                }
            }
            \Yii::error(['is_sale:' . $is_sale, 'is_refund:' . $is_refund]);
            //礼物完成，更改礼物订单售后状态
            if ($is_sale && $is_refund) {
                if (empty($gift_order)) {
                    \Yii::error('礼物订单未找到');
                    return false;
                }
                if ($is_order) {
                    if (Order::updateAll(['is_sale' => 1, 'is_send' => 1, 'is_confirm' => 1], ['order_no' => $gift_order->order_no]) > 0) {
                        \Yii::error('礼物订单售后状态修改成功');
                        self::dealBonus($gift_order->order_no);
                        return true;
                    }
                } else {
                    if (Order::updateAll(['cancel_status' => 1], ['order_no' => $gift_order->order_no]) > 0) {
                        \Yii::error('礼物订单售后状态修改成功');
                        self::dealBonus($gift_order->order_no);
                        return true;
                    }
                }
            }
        } else {
            if (Order::updateAll(['cancel_status' => 1], ['order_no' => $gift_order->order_no]) > 0) {
                \Yii::error('礼物订单售后状态修改成功');
                self::dealBonus($gift_order->order_no);
                return true;
            }
        }

        return false;
    }

    private static function dealBonus($giftOrderNo)
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('bonus');
            $order = Order::findOne(['order_no' => $giftOrderNo]);
            $plugin->setBonusOrderLog($order);
        } catch (\Exception $e) {
            \Yii::error('========礼物订单处理团队分红======');
            \Yii::error($e);
        }
    }

    /**
     * @param GiftSendOrder $giftOrder
     * @param GiftLog $logModel
     * @param $cancel_status 0
     * @param $pay 1
     * @throws \Exception
     * 买礼物的订单支付后添加到商城订单中
     */
    public static function setOrder($giftOrder, $logModel, $cancel_status = 0, $pay = 1)
    {
        $order = new Order();
        $order->order_no = $giftOrder->order_no;
        $order->token = \Yii::$app->security->generateRandomString();
        $order->mall_id = $giftOrder->mall_id;
        $order->mch_id = $giftOrder->mch_id;
        $order->user_id = $giftOrder->user_id;
        $order->total_price = $giftOrder->total_price;
        $order->total_pay_price = $giftOrder->total_pay_price;
        $order->express_original_price = 0;
        $order->express_price = 0;
        // TODO 数据暂时没有
        $order->total_goods_original_price = $giftOrder->total_goods_original_price;
        $order->total_goods_price = $giftOrder->total_goods_price;
        $order->member_discount_price = $giftOrder->member_discount_price;
        $order->full_reduce_price = $giftOrder->full_reduce_price;
        $order->use_user_coupon_id = $giftOrder->use_user_coupon_id;
        $order->coupon_discount_price = $giftOrder->coupon_discount_price;
        $order->use_integral_num = $giftOrder->use_integral_num;
        $order->integral_deduction_price = $giftOrder->integral_deduction_price;

        $order->remark = '';
        $order->order_form = \Yii::$app->serializer->encode([]);
        $order->words = '';
        $order->is_pay = $pay;
        $order->pay_type = $giftOrder->pay_type;
        $order->pay_time = $giftOrder->pay_time;
        $order->status = 0;
        $order->is_send = 0;
        $order->is_confirm = 0;
        $order->is_sale = 0;
        $order->cancel_status = $cancel_status;
        $order->is_comment = 1;
        $order->sign = (new Plugin())->getName();
        $order->support_pay_types = $giftOrder->support_pay_types;
        if (!$order->save()) {
            throw new \Exception((new Model())->getErrorMsg($order));
        }
        $logModel->order_id = $order->id;
        if (!$logModel->save()) {
            throw new \Exception((new Model())->getErrorMsg($logModel));
        }
        $detail = $giftOrder->detail;
        $orderDetailList = [];
        foreach ($detail as $item) {
            if ($logModel->open_type == 1) {
                $orderDetail = [];
                for ($i = 0; $i < $item->num; $i++) {
                    $orderDetail = self::setOrderDetail($item, 1, $order, $item->num);
                    $orderDetailList[] = $orderDetail;
                }
            } else {
                $orderDetail = self::setOrderDetail($item, $item->num, $order, $item->num);
                $orderDetailList[] = $orderDetail;
            }
        }
        if (!empty($orderDetail)) {
            \Yii::$app->db->createCommand()->batchInsert(
                OrderDetail::tableName(),
                array_keys((new OrderDetail())->attributes),
                $orderDetailList
            )->execute();
        } else {
            throw new \Exception('订单创建失败');
        }
        if ($cancel_status == 0) {
            \Yii::$app->trigger(Order::EVENT_PAYED, new OrderEvent([
                'order' => $order,
                'type' => 2
            ]));
        }
    }

    /**
     * @param GiftSendOrderDetail $item
     * @param integer $num
     * @param Order $order
     * @param int $total
     * @return array
     */
    private static function setOrderDetail($item, $num, $order, $total)
    {
        $goodsInfo = \Yii::$app->serializer->decode($item->goods_info);
        $orderDetail = new OrderDetail();
        $orderDetail->order_id = $order->id;
        $orderDetail->goods_id = $item->goods_id;
        $orderDetail->num = $num;
        $orderDetail->unit_price = $item->unit_price;
        $orderDetail->total_original_price = price_format($item->total_original_price * ($num / $total));
        $orderDetail->total_price = price_format($item->total_price * ($num / $total));

        // TODO 数据暂时没有
        $orderDetail->member_discount_price = price_format($item->member_discount_price * ($num / $total));
//        $orderDetail->form_id = 0;

        $orderDetail->goods_info = $item->goods_info;
        $orderDetail->is_refund = $item->is_refund;
        $orderDetail->refund_status = $item->refund_status;
        $orderDetail->sign = (new Plugin())->getName();
        $orderDetail->goods_no = $goodsInfo['goods_attr']['no'];
        $orderDetail->is_delete = 0;
        $orderDetail->back_price = 0;
        $orderDetail->refund_status = 0;
        $orderDetail->created_at = mysql_timestamp();
        $orderDetail->deleted_at = '0000-00-00 00:00:00';
        $orderDetail->updated_at = '0000-00-00 00:00:00';
        $orderDetail->form_id = 0;
        $orderDetail->goods_type = 'goods';
        return $orderDetail->attributes;
    }


    public static function sendRefundMsg($orderRefund, $refund_price, $remark)
    {
        $tplMsg = new Tplmsg();
        $tplMsg->giftOrderRefundMsg($orderRefund, $refund_price, $remark);
    }
}
