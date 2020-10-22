<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%payment_order}}".
 *
 * @property int $id
 * @property int $payment_order_union_id
 * @property string $order_no
 * @property string $amount
 * @property int $is_pay 支付状态：0=未支付，1=已支付
 * @property int $pay_type 支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付，5=百度支付，6=头条支付
 * @property string $title
 * @property string $created_at
 * @property string $updated_at
 * @property string $notify_class
 * @property string $refund 已退款金额
 * @property Order $order
 * @property PaymentOrderUnion paymentOrderUnion
 */
class PaymentOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_order_union_id', 'order_no', 'amount', 'title', 'notify_class'], 'required'],
            [['payment_order_union_id', 'is_pay', 'pay_type'], 'integer'],
            [['amount', 'refund'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_no'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 128],
            [['notify_class'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_order_union_id' => 'Payment Order Union ID',
            'order_no' => 'Order No',
            'amount' => 'Amount',
            'is_pay' => '支付状态：0=未支付，1=已支付',
            'pay_type' => '支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付',
            'title' => 'Title',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'notify_class' => 'Notify Class',
            'refund' => '已退款金额',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_no' => 'order_no']);
    }

    public function getPaymentOrderUnion()
    {
        return $this->hasOne(PaymentOrderUnion::className(), ['id' => 'payment_order_union_id']);
    }
}
