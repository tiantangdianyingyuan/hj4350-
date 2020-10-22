<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 */


namespace app\plugins\gift\jobs;


use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\gift\forms\api\GiftOrderSubmitForm;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrderSubmitResult;
use app\plugins\gift\models\GiftSendOrder;
use app\plugins\mch\models\MchOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GiftOrderSubmitJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var User $user */
    public $user;

    /** @var array $data */
    public $form_data;

    /** @var string $token */
    public $token;

    public $auto_refund;
    public $auto_remind;

    public $type;
    public $open_time;
    public $open_num;
    public $open_type;
    public $bless_word;
    public $bless_music;

    public $sign;
    public $supportPayTypes;
    public $enableMemberPrice;
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

    public $enableFullReduce;

    /***
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    public function execute($queue)
    {
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setMall($this->mall);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->user->userInfo->platform);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $oldOrder = GiftSendOrder::findOne(['token' => $this->token]);
            if ($oldOrder) {
                throw new \Exception('重复下单。');
            }
            /** @var GiftOrderSubmitForm $form */
            $form = new $this->OrderSubmitFormClass();
            $form->form_data = $this->form_data;
            $form->setSign($this->sign)
                ->setEnableMemberPrice($this->enableMemberPrice)
                ->setEnableCoupon($this->enableCoupon)
                ->setEnableIntegral($this->enableIntegral)
                ->setEnablePriceEnable($this->enablePriceEnable)
                ->setEnableVipPrice($this->enableVipPrice)
                ->setEnableAddressEnable($this->enableAddressEnable)
                ->setEnableOrderForm($this->enableOrderForm)
                ->setEnableFullReduce($this->enableFullReduce);

            $data = $form->getAllData();
            $goods_num = 0;
            $log_model = new GiftLog();
            $log_model->mall_id = \Yii::$app->mall->id;
            $log_model->user_id = \Yii::$app->user->id;
            $log_model->num = $goods_num;
            $log_model->type = $this->type;
            $log_model->open_time = !$this->open_time ? '0000-00-00 00:00:00' : $this->open_time;
            $log_model->open_num = !$this->open_num ? 0 : $this->open_num;
            $log_model->open_type = $this->open_type;
            $log_model->bless_word = $this->bless_word;
            $log_model->bless_music = $this->bless_music;
            if ($this->type == 'time_open') {
                $log_model->auto_refund_time = $this->open_time;
            } else {
                $log_model->auto_refund_time = date('Y-m-d H:i:s', time() + $this->auto_refund * 24 * 60 * 60);
            }
            if (!$log_model->save()) {
                throw new \Exception($log_model->errors[0]);
            }
            $order_no = date('YmdHis') . rand(100000, 999999);
            foreach ($data['mch_list'] as $mchItem) {
                $order = new GiftSendOrder();

                $order->mall_id = \Yii::$app->mall->id;
                $order->user_id = \Yii::$app->user->identity->getId();
                $order->mch_id = $mchItem['mch']['id'];

                $order->gift_id = $log_model->id;

                $order->order_no = $order_no;

                $order->total_price = $mchItem['total_price'];
                $order->total_pay_price = $mchItem['total_price'];
                $order->total_goods_price = $mchItem['total_goods_price'];
                $order->total_goods_original_price = $mchItem['total_goods_original_price'];

                $order->member_discount_price = $mchItem['member_discount'];
                $order->full_reduce_price = $mchItem['full_reduce_discount'];
                $order->use_user_coupon_id = $mchItem['coupon']['use'] ? $mchItem['coupon']['user_coupon_id'] : 0;
                $order->coupon_discount_price = $mchItem['coupon']['coupon_discount'];
                $order->use_integral_num = $mchItem['integral']['use'] ? $mchItem['integral']['use_num'] : 0;
                $order->integral_deduction_price = $mchItem['integral']['use'] ?
                    $mchItem['integral']['deduction_price'] : 0;

                $order->is_pay = 0;
                $order->pay_type = 0;
                $order->is_confirm = 0;
                $order->support_pay_types = \Yii::$app->serializer->encode($this->supportPayTypes);
                $order->token = $this->token;

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

                foreach ($mchItem['goods_list'] as $goodsItem) {
                    $form->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $form->extraGoodsDetail($order, $goodsItem);
                    $goods_num += $goodsItem['num'];
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
                    if (!\Yii::$app->currency->integral->sub($order->use_integral_num, '下单积分抵扣')) {
                        throw new \Exception('积分操作失败。');
                    }
                }
            }
            //更新礼物数量
            $log_model->num = $goods_num;
            if (!$log_model->save()) {
                throw new \Exception((new Model())->getErrorMsg($log_model));
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error($e->getMessage());
            \Yii::error($e);
            $orderSubmitResult = new GiftOrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $e->getMessage();
            $orderSubmitResult->save();
            throw $e;
        }
    }
}
