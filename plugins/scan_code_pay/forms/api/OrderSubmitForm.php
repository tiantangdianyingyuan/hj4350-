<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;

use app\core\payment\PaymentOrder;
use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\api\order\OrderPayNotify;
use app\jobs\OrderCancelJob;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserInfo;
use app\plugins\scan_code_pay\forms\common\CommonActivityForm;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;
use app\plugins\scan_code_pay\models\ScanCodePayActivities;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroups;
use app\plugins\scan_code_pay\models\ScanCodePayActivitiesGroupsRules;
use app\plugins\scan_code_pay\models\ScanCodePayOrders;
use app\plugins\step\models\GoodsAttr;
use yii\helpers\ArrayHelper;

class OrderSubmitForm extends Model
{
    public $price;
    public $coupon_id;
    public $use_integral;
    public $remark;

    public function rules()
    {
        return [
            [['price', 'coupon_id', 'use_integral'], 'required'],
            [['use_integral', 'coupon_id'], 'integer'],
            [['remark'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'price' => '金额',
            'coupon_id' => '优惠券ID',
            'use_integral' => '积分抵扣'
        ];
    }

    public function preview()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->price <= 0) {
                throw new \Exception('金额不能小于0');
            }
            $data = $this->getPreferential();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function getPreferential()
    {
        $scanCodePaySetting = (new CommonScanCodePaySetting())->getSetting();
        if (!$scanCodePaySetting['is_scan_code_pay']) {
            throw new \Exception('当面付活动未开启');
        }

        if ($this->price < 0) {
            throw new \Exception('输入金额不能小于0');
        }

        /** @var ScanCodePayActivities $activity */
        $activity = (new CommonActivityForm())->search();
        // 当前没有买单活动 则按正常下单 不计算优惠
        $data = [
            'activity_id' => 0,
            'activity_group_id' => 0,
            'integral_explain' => [],
            'price' => (float)$this->price,
            'pay_price' => (float)$this->price,
            'coupon_id' => 0,
            'preferential_money' => 0,
            'integral_deduction' => 0,
            'use_integral' => (int)$this->use_integral,
            'use_integral_num' => 0,
            'coupon_preferential_money' => 0,
            'send_integral_num' => 0,
            'send_integral_type' => 1,
            'send_balance' => 0,
            'send_coupons' => [],
            'send_cards' => [],
            'can_coupon_num' => 0,
        ];

        if ($activity) {
            $data['activity_id'] = $activity->id;
            // 查询用户属于哪个组
            $memberLevel = \Yii::$app->user->identity->identity->member_level;
            $userByGroup = $this->getUserByGroup($activity, $memberLevel);
            if (!$userByGroup) {
                return $data;
            }
            $data['activity_group_id'] = $userByGroup->id;
            $currentPrice = $this->getRulesByPrice($userByGroup, $this->price);
            // 积分抵扣规则说明
            $integralExplain = $this->getIntegralExplain($userByGroup);
            $data['integral_explain'] = $integralExplain;

            /** @var ScanCodePayActivitiesGroupsRules $rule */
            foreach ($userByGroup->rules as $rule) {
                // 满足优惠条件
                if ($rule->rules_type == 2 && (float)$rule->consume_money == (float)$currentPrice) {
                    // 优惠金额
                    $data['preferential_money'] = $rule->preferential_money;
                    $payPrice = (float)$this->price - (float)$data['preferential_money'];
                    $data['pay_price'] = $payPrice > 0 ? $payPrice : 0;

                    // 优惠券金额计算
                    $couponData = $this->getCouponMoney($rule);
                    $data['coupon_preferential_money'] = $couponData['coupon_money'];
                    $data['coupon_id'] = $couponData['coupon_id'];
                    // 先判断规则是否可使用优惠券
                    $data['can_coupon_num'] = $rule->is_coupon ? $couponData['can_coupon_num'] : 0;
                    $payPrice = (float)$data['pay_price'] - (float)$data['coupon_preferential_money'];
                    $data['pay_price'] = $payPrice > 0 ? $payPrice : 0;

                    // 积分抵扣
                    $integralArr = $this->getIntegralDeduction($rule, $data['pay_price']);
                    $data['integral_deduction'] = $integralArr['integral_deduction'];
                    $data['use_integral_num'] = $integralArr['use_integral_num'];
                    // 用户是否使用积分抵扣
                    if ($this->use_integral) {
                        // 如果使用积分为0 则标记为不使用积分抵扣
                        $data['use_integral'] = $data['use_integral_num'] > 0 ? 1 : 0;
                        $payPrice = (float)$data['pay_price'] - (float)$data['integral_deduction'];
                        $data['pay_price'] = $payPrice > 0 ? $payPrice : 0;
                    }
                }
            }

            $data = $this->sendData($activity, $userByGroup, $data);
        }

        $data['activity_id'] = (int)$data['activity_id'];
        $data['activity_group_id'] = (int)$data['activity_group_id'];
        $data['price'] = price_format($data['price']);
        $data['pay_price'] = price_format($data['pay_price']);
        $data['coupon_id'] = (int)$data['coupon_id'];
        $data['use_integral'] = (int)$data['use_integral'];
        $data['preferential_money'] = price_format($data['preferential_money']);
        $data['integral_deduction'] = price_format($data['integral_deduction']);
        $data['use_integral_num'] = (int)$data['use_integral_num'];
        $data['coupon_preferential_money'] = price_format($data['coupon_preferential_money']);
        $data['send_integral_num'] = (int)$data['send_integral_num'];
        $data['send_integral_type'] = (int)$data['send_integral_type'];
        $data['send_balance'] = price_format($data['send_balance']);

        return $data;
    }

    /**
     * 积分抵扣规则 说明
     * @param ScanCodePayActivitiesGroups $group
     * @return array
     */
    private function getIntegralExplain($group)
    {
        $consumeMoneyArr = [];
        /** @var ScanCodePayActivitiesGroupsRules $rule */
        foreach ($group->rules as $rule) {
            if ($rule->rules_type == 2) {
                $consumeMoneyArr[] = $rule->consume_money;
            }
        }
        sort($consumeMoneyArr);

        $dataArr = [];
        foreach ($consumeMoneyArr as $item) {
            foreach ($group->rules as $rule) {
                if ($rule->consume_money == $item && $rule->rules_type == 2) {
                    $arr = [];
                    $arr['consume_money'] = $rule->consume_money;
                    $arr['integral_deduction'] = 0;
                    if ($rule->integral_deduction_type == 1) {
                        $arr['integral_deduction'] = $rule->integral_deduction;
                    } else {
                        $arr['integral_deduction'] = $this->price * ($rule->integral_deduction / 100);
                    }
                    $dataArr[] = $arr;
                }
            }
        }

        return $dataArr;
    }

    /**
     * 计算赠送 规则
     * @param $activity
     * @param $group
     * @param $data
     * @return array
     */
    private function sendData($activity, $group, $data)
    {
        $sendData = [
            'send_integral_num' => 0,
            'send_balance' => 0,
            'send_coupons' => [],
            'send_cards' => []
        ];
        /** @var ScanCodePayActivitiesGroupsRules $rule */
        foreach ($group->rules as $rule) {
            if ($rule->rules_type != 1) {
                continue;
            }
            if ($activity->send_type == 1) {
                // 赠送所有规则
                if ($data['pay_price'] >= $rule->consume_money) {
                    if ($rule->send_integral_type == 1) {
                        $sendData['send_integral_num'] += $rule->send_integral_num;
                    } else {
                        $sendData['send_integral_num'] += $data['pay_price'] * ($rule->send_integral_num / 100);
                    }
                    $sendData['send_balance'] += (float)$rule->send_money;
                    // TODO 这里同样的优惠券是否要累计数量
                    foreach ($rule->scanCards as $scanCard) {
                        $sendData['send_cards'][] = ArrayHelper::toArray($scanCard);
                    }
                    foreach ($rule->scanCoupons as $scanCoupon) {
                        $sendData['send_coupons'][] = ArrayHelper::toArray($scanCoupon);
                    }
                }
            } else {
                // 赠送满足最高规则
                $rulesByPrice = $this->getRulesByPrice($group, $data['pay_price'], 1);
                if ($rule->consume_money == $rulesByPrice) {
                    if ($rule->send_integral_type == 1) {
                        $sendData['send_integral_num'] = $rule->send_integral_num;
                    } else {
                        $sendData['send_integral_num'] = $data['pay_price'] * ($rule->send_integral_num / 100);
                    }

                    $sendData['send_balance'] = $rule->send_money;
                    foreach ($rule->scanCards as $scanCard) {
                        $sendData['send_cards'][] = ArrayHelper::toArray($scanCard);
                    }
                    foreach ($rule->scanCoupons as $scanCoupon) {
                        $sendData['send_coupons'][] = ArrayHelper::toArray($scanCoupon);
                    }
                }
            }
        }

        $data = array_merge($data, $sendData);

        return $data;
    }

    /**
     * 计算优惠券 优惠
     * @param $rule
     * @return array
     * @throws \Exception
     */
    private function getCouponMoney($rule)
    {
        $couponMoney = 0;
        $couponId = 0;
        if ($rule->is_coupon && $this->coupon_id > 0) {
            /** @var UserCoupon $userCoupon */
            $userCoupon = UserCoupon::find()->where([
                'id' => $this->coupon_id,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'is_use' => 0,
            ])->with('coupon')->one();

            if (!$userCoupon) {
                throw new \Exception('优惠券不存在或已失效');
            }

            if ($userCoupon->coupon->appoint_type != 4) {
                throw new \Exception('该优惠券不可用于当面付');
            }

            if (time() > strtotime($userCoupon->end_time)) {
                throw new \Exception('优惠券已过期');
            }

            if (time() < strtotime($userCoupon->start_time)) {
                throw new \Exception('优惠券未到使用日期');
            }

            if ($this->price < $userCoupon->coupon_min_price) {
                throw new \Exception('优惠券最低消费金额' . $userCoupon->coupon_min_price);
            }

            if ($userCoupon->type == 1) {
                // 折扣
                $couponMoney = $this->price * (1 - $userCoupon->discount / 10);
                $couponMoney = !empty($userCoupon->discount_limit) && ($userCoupon->discount_limit < $couponMoney) ? $userCoupon->discount_limit : $couponMoney;
            } else {
                // 满减
                $couponMoney = $userCoupon->sub_price;
            }
            $couponId = $userCoupon->id;
        }

        $couponForm = new CouponsForm();
        $couponForm->price = $this->price;
        $list = $couponForm->getList();

        return [
            'can_coupon_num' => count($list),
            'coupon_money' => (float)$couponMoney,
            'coupon_id' => $couponId,
        ];

    }

    /**
     * 计算积分抵扣
     * @param $rule
     * @param $payPrice
     * @return array
     */
    private function getIntegralDeduction($rule, $payPrice)
    {
        $data = [
            'integral_deduction' => 0,
            'use_integral_num' => 0,
        ];

        // 积分抵扣
        if ($rule->integral_deduction_type == 1) {
            // 积分类型 固定值
            $integralDeduction = $rule->integral_deduction;
        } else {
            // 积分类型 百分比
            $integralDeduction = $this->price * ($rule->integral_deduction / 100);
        }
        $userInfo = UserInfo::findOne(['user_id' => \Yii::$app->user->id]);
        $memberIntegral = (new Mall())->getMallSettingOne('member_integral');
        if ($userInfo['integral'] > 0 && $memberIntegral > 0) {
            $canIntegralDeduction = $userInfo['integral'] / $memberIntegral;
            $integralDeduction = $integralDeduction > $canIntegralDeduction ? $canIntegralDeduction : $integralDeduction;
            // 积分抵扣如果大于支付金额 那么积分最多只能抵扣和支付金额相同值
            $data['integral_deduction'] = $integralDeduction > $payPrice ? $payPrice : $integralDeduction;
            $data['use_integral_num'] = ceil($data['integral_deduction'] * $memberIntegral);
        }

        return $data;
    }

    /**
     * 活动规则 优惠|赠送
     * @param $userByGroup
     * @param $price
     * @param $type 1.赠送规则 2.优惠规则
     * @return mixed|null
     */
    private function getRulesByPrice($userByGroup, $price, $type = 2)
    {
        $priceArr = [];
        /** @var ScanCodePayActivitiesGroupsRules $rule */
        foreach ($userByGroup->rules as $rule) {
            // 优惠规则
            if ($rule->rules_type == $type) {
                $priceArr[] = $rule->consume_money;
            }
        }
        sort($priceArr);

        $currentPrice = null;
        foreach ($priceArr as $item) {
            if ((float)$price >= (float)$item) {
                $currentPrice = $item;
            }
        }

        return $currentPrice;
    }

    /**
     * 获取用户 属于哪个活动组
     * @param $activity
     * @param $memberLevel
     * @return ScanCodePayActivitiesGroups|array
     */
    public function getUserByGroup($activity, $memberLevel)
    {
        /** @var ScanCodePayActivitiesGroups $group */
        $newGroup = [];
        $commonGroup = [];
        foreach ($activity->groups as $group) {
            /** @var MallMembers $member */
            foreach ($group->scanMembers as $member) {
                if ($member->level == 0) {
                    $commonGroup = $group;
                }
                // 找出用户属于哪一个活动组
                if ($member->level == $memberLevel) {
                    // 如果会员被删除或者禁用，则不享受优惠
                    if ($memberLevel > 0) {
                        $mallMember = MallMembers::find()->where([
                            'mall_id' => \Yii::$app->mall->id,
                            'status' => 1,
                            'is_delete' => 0,
                            'level' => $memberLevel
                        ])->one();
                        if ($mallMember) {
                            $newGroup = $group;
                        }
                    } else {
                        $newGroup = $group;
                    }
                }
            }
        }

        if ($memberLevel > 0 && !$newGroup) {
            return $commonGroup;
        }

        return $newGroup;
    }

    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $data = $this->getPreferential();
            $scanCodePaySetting = (new CommonScanCodePaySetting())->getSetting();
            $order = new Order();
            $order->mall_id = \Yii::$app->mall->id;
            $order->user_id = \Yii::$app->user->id;
            $order->order_no = date('YmdHis') . rand(100000, 999999);
            $order->total_price = $data['price'];
            $order->total_pay_price = $data['pay_price'];
            $order->express_original_price = 0;
            $order->express_price = 0;
            $order->total_goods_price = $data['pay_price'];
            $order->total_goods_original_price = $data['price'];

            $order->member_discount_price = 0;
            $order->use_user_coupon_id = $this->coupon_id;
            $order->coupon_discount_price = $data['coupon_preferential_money'];
            $order->use_integral_num = $this->use_integral ? $data['use_integral_num'] : 0;
            $order->integral_deduction_price = $this->use_integral ? $data['integral_deduction'] : 0;
            $order->remark = $this->remark;
            $order->order_form = \Yii::$app->serializer->encode([]);
            $order->words = '';

            $order->is_pay = 0;
            $order->pay_type = 0;
            $order->is_send = 0;
            $order->is_confirm = 0;
            $order->is_sale = 0;
            $order->support_pay_types = \Yii::$app->serializer->encode($scanCodePaySetting['payment_type']);

            $order->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
            $order->token = \Yii::$app->security->generateRandomString();
            $order->status = 1;
            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }

            $this->saveScanCodePayOrder($order->id, $data['preferential_money']);
            $this->saveOrderDetail($order, $data);

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
                \Yii::$app->getCurrency()->user = User::findOne(\Yii::$app->user->id);
                $customDesc = \Yii::$app->serializer->encode($order->attributes);
                if (!\Yii::$app->currency->integral->sub((int)$order->use_integral_num, '当面付下单积分抵扣', $customDesc)) {
                    throw new \Exception('积分操作失败。');
                }
            }

            $event = new OrderEvent();
            $event->order = $order;
            $event->sender = $this;
            \Yii::$app->trigger(Order::EVENT_CREATED, $event);

            // 5分钟后取消订单
            $queueId = \Yii::$app->queue->delay(5 * 60)->push(new OrderCancelJob([
                'orderId' => $order->id
            ]));

            $supportPayTypes = $this->getSupportPayTypes($scanCodePaySetting);
            $payOrder = new PaymentOrder([
                'title' => '当面付',
                'amount' => floatval($order->total_pay_price),
                'orderNo' => $order->order_no,
                'notifyClass' => OrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $id = \Yii::$app->payment->createOrder($payOrder);

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单创建成功',
                'data' => [
                    'pay_id' => $id
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function saveScanCodePayOrder($orderId, $preferentialPrice)
    {
        $scanOrder = new ScanCodePayOrders();
        $scanOrder->order_id = $orderId;
        $scanOrder->activity_preferential_price = (string)$preferentialPrice;
        $res = $scanOrder->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($scanOrder));
        }
    }

    private function getSupportPayTypes($scanCodePaySetting)
    {
        $arr = [];
        foreach ($scanCodePaySetting['payment_type'] as $item) {
            if ($item == 'online_pay') {
                $arr[] = \app\core\payment\Payment::PAY_TYPE_WECHAT;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_ALIPAY;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_BAIDU;
                $arr[] = \app\core\payment\Payment::PAY_TYPE_TOUTIAO;
            }
            if ($item == 'balance') {
                $arr[] = \app\core\payment\Payment::PAY_TYPE_BALANCE;
            }
        }

        return $arr;
    }

    /**
     * @param $order
     * @param $data
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
        $orderDetail->unit_price = $data['pay_price'];
        $orderDetail->total_original_price = $data['price'];
        $orderDetail->total_price = $data['pay_price'];
        $orderDetail->member_discount_price = 0;
        $orderDetail->sign = \Yii::$app->plugin->currentPlugin->getName();

        $attrGroups = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = [];
        foreach ($attrGroups as $attrGroup) {
            $arr['attr_group_id'] = $attrGroup['attr_group_id'];
            $arr['attr_group_name'] = $attrGroup['attr_group_name'];
            $arr['attr_id'] = $attrGroup['attr_list'][0]['attr_id'];
            $arr['attr_name'] = $attrGroup['attr_list'][0]['attr_name'];
            $attrList[] = $arr;
        }

        $orderGoodsAttr = new OrderGoodsAttr();
        $orderGoodsAttr->goods = $goods;
        $orderGoodsAttr->goodsAttr = $goods->attr[0];

        $orderGoodsAttr->integral_price = $this->use_integral ? $data['integral_deduction'] : 0;
        $orderGoodsAttr->use_integral = $this->use_integral ? $data['use_integral_num'] : 0;
        $goodsInfo = [
            'attr_list' => $attrList,
            'goods_attr' => $orderGoodsAttr,
            'rules_data' => $data
        ];
        $orderDetail->goods_info = $orderDetail->encodeGoodsInfo($goodsInfo);

        if (!$orderDetail->save()) {
            throw new \Exception((new Model())->getErrorMsg($orderDetail));
        }

        return true;
    }
}
