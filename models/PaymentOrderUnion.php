<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%payment_order_union}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $order_no
 * @property string $amount
 * @property int $is_pay 支付状态：0=未支付，1=已支付
 * @property int $pay_type 支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付，5=百度支付，6=头条支付
 * @property string $title
 * @property string $support_pay_types 支持的支付方式（JSON）
 * @property string $created_at
 * @property string $updated_at
 * @property string $app_version 小程序端版本
 * @property $paymentOrder
 */
class PaymentOrderUnion extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_order_union}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_no', 'amount', 'title'], 'required'],
            [['mall_id', 'user_id', 'is_pay', 'pay_type'], 'integer'],
            [['amount'], 'number'],
            [['support_pay_types'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_no', 'app_version'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 128],
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
            'user_id' => 'User ID',
            'order_no' => 'Order No',
            'amount' => 'Amount',
            'is_pay' => '支付状态：0=未支付，1=已支付',
            'pay_type' => '支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付',
            'title' => 'Title',
            'support_pay_types' => '支持的支付方式（JSON）',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'app_version' => '小程序端版本',
        ];
    }

    public function encodeSupportPayTypes($data)
    {
        return Yii::$app->serializer->encode($data);
    }

    public function decodeSupportPayTypes($data)
    {
        return Yii::$app->serializer->decode($data);
    }

    public function getPaymentOrder()
    {
        return $this->hasMany(PaymentOrder::className(), ['payment_order_union_id' => 'id']);
    }
}
