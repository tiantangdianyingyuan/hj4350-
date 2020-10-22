<?php

namespace app\plugins\gift\models;

use Yii;

/**
 * This is the model class for table "{{%gift_send_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property string $order_no
 * @property int $gift_id
 * @property string $total_price 订单总金额(含运费)
 * @property string $total_pay_price 实际支付总费用(含运费）
 * @property string $total_goods_price
 * @property string $total_goods_original_price
 * @property string $member_discount_price
 * @property string $full_reduce_price 满减活动优惠价格
 * @property string $use_user_coupon_id
 * @property string $coupon_discount_price
 * @property string $use_integral_num
 * @property string $integral_deduction_price
 * @property int $is_pay 是否支付：0.未支付|1.已支付
 * @property int $pay_type 支付方式：1.在线支付 2.货到付款 3.余额支付
 * @property string $pay_time 支付时间
 * @property int $is_refund 0未退款，1已退款
 * @property int $is_confirm 送礼状态：0.未完成送礼|1.已完成送礼
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $support_pay_types 支持的支付方式，空表示支持系统设置支持的所有方式
 * @property string $token
 * @property int $is_cancel
 * @property GiftSendOrderDetail[] detail
 */
class GiftSendOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_send_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'user_id', 'gift_id', 'is_pay', 'pay_type', 'is_refund', 'is_confirm', 'is_delete', 'use_user_coupon_id', 'use_integral_num', 'is_cancel'], 'integer'],
            [['total_price', 'total_pay_price', 'support_pay_types', 'token'], 'required'],
            [['total_price', 'total_pay_price', 'total_goods_price', 'total_goods_original_price', 'member_discount_price', 'full_reduce_price', 'coupon_discount_price', 'integral_deduction_price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['support_pay_types'], 'string'],
            [['order_no'], 'string', 'max' => 60],
            [['token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'user_id' => 'User ID',
            'order_no' => 'Order No',
            'gift_id' => 'Gift ID',
            'total_price' => '订单总金额(含运费)',
            'total_pay_price' => '实际支付总费用(含运费）',
            'total_goods_price' => 'total_goods_price',
            'total_goods_original_price' => 'total_goods_original_price',
            'member_discount_price' => 'member_discount_price',
            'full_reduce_price' => '满减活动优惠价格',
            'use_user_coupon_id' => 'use_user_coupon_id',
            'coupon_discount_price' => 'coupon_discount_price',
            'use_integral_num' => 'use_integral_num',
            'integral_deduction_price' => 'integral_deduction_price',
            'is_pay' => '是否支付：0.未支付|1.已支付',
            'pay_type' => '支付方式：1.在线支付 2.货到付款 3.余额支付',
            'pay_time' => '支付时间',
            'is_refund' => '0未退款，1已退款',
            'is_confirm' => '送礼状态：0.未完成送礼|1.已完成送礼',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'support_pay_types' => '支持的支付方式，空表示支持系统设置支持的所有方式',
            'token' => 'Token',
            'is_cancel' => 'Is Cancel',
        ];
    }

    public function decodeSupportPayTypes($data)
    {
        return Yii::$app->serializer->decode($data);
    }

    public function getDetail()
    {
        return $this->hasMany(GiftSendOrderDetail::className(), ['send_order_id' => 'id']);
    }
}
