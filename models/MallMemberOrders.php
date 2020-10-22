<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mall_member_orders}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $mall_id
 * @property string $order_no 订单号
 * @property string $pay_price 支付金额
 * @property int $pay_type 支付方式 1.线上支付
 * @property int $is_pay 是否支付 0--未支付 1--支付
 * @property string $pay_time 支付时间
 * @property string $detail 会员更新详情
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class MallMemberOrders extends ModelActiveRecord
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
        return '{{%mall_member_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'pay_price', 'pay_type', 'detail',
                'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'mall_id', 'pay_type', 'is_pay', 'is_delete'], 'integer'],
            [['pay_price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['detail'], 'string'],
            [['order_no'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'mall_id' => 'Mall ID',
            'order_no' => 'Order No',
            'pay_price' => 'Pay Price',
            'pay_type' => 'Pay Type',
            'is_pay' => 'Is Pay',
            'pay_time' => 'Pay Time',
            'detail' => 'Detail',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
