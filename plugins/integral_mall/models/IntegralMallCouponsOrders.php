<?php

namespace app\plugins\integral_mall\models;

use Yii;

/**
 * This is the model class for table "{{%integral_mall_coupons_orders}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $mall_id
 * @property string $order_no
 * @property int $integral_mall_coupon_id 积分商城优惠券ID
 * @property string $integral_mall_coupon_info 积分商城优惠券信息
 * @property int $user_coupon_id 关联用户优惠券ID
 * @property string $price 优惠券价格
 * @property int $integral_num 优惠券积分
 * @property int $is_pay
 * @property string $pay_time
 * @property int $pay_type 支付方式：1.在线支付 2.货到付款 3.余额支付
 * @property string $token
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class IntegralMallCouponsOrders extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_coupons_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'integral_mall_coupon_id', 'integral_mall_coupon_info', 'user_coupon_id', 'price', 'integral_num', 'token', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'mall_id', 'integral_mall_coupon_id', 'user_coupon_id', 'integral_num', 'is_pay', 'pay_type', 'is_delete'], 'integer'],
            [['integral_mall_coupon_info'], 'string'],
            [['price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['order_no', 'token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'mall_id' => 'Mall ID',
            'order_no' => 'Order No',
            'integral_mall_coupon_id' => '积分商城优惠券ID',
            'integral_mall_coupon_info' => '积分商城优惠券信息',
            'user_coupon_id' => '关联用户优惠券ID',
            'price' => '优惠券价格',
            'integral_num' => '优惠券积分',
            'is_pay' => 'Is Pay',
            'pay_time' => 'Pay Time',
            'pay_type' => '支付方式：1.在线支付 2.货到付款 3.余额支付',
            'token' => 'Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getIntegralCoupon()
    {
        return $this->hasOne(IntegralMallCoupons::className(), ['id' => 'integral_mall_coupon_id']);
    }
}
