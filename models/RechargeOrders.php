<?php

namespace app\models;

/**
 * This is the model class for table "{{%recharge_orders}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $order_no
 * @property int $user_id
 * @property string $pay_price
 * @property string $send_price
 * @property int $pay_type
 * @property int $is_pay
 * @property string $pay_time
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $send_integral 赠送的积分
 * @property int $send_member_id 赠送的积分
 */
class RechargeOrders extends ModelActiveRecord
{
    /**
     * 支付方式: 线上支付
     */
    const PAY_TYPE_ON_LINE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'pay_price', 'send_price', 'pay_type', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'pay_type', 'is_pay', 'is_delete', 'send_integral', 'send_member_id'], 'integer'],
            [['pay_price', 'send_price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['order_no'], 'string', 'max' => 32],
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
            'order_no' => 'Order No',
            'user_id' => 'User ID',
            'pay_price' => 'Pay Price',
            'send_price' => 'Send Price',
            'pay_type' => 'Pay Type',
            'is_pay' => 'Is Pay',
            'pay_time' => 'Pay Time',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'send_integral' => '赠送的积分',
            'send_member_id' => '赠送的会员',
        ];
    }
}
