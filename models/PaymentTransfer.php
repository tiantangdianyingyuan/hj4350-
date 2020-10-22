<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%payment_transfer}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $order_no 提交微信或支付宝的订单号
 * @property string $transfer_order_no 发起 打款的订单号
 * @property string $amount 金额
 * @property int $is_pay 支付状态 0--未支付|1--已支付
 * @property string $pay_type 方式：wechat--微信打款 alipay--支付宝打款
 * @property string $title
 * @property string $created_at
 * @property string $updated_at
 */
class PaymentTransfer extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_transfer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'order_no', 'transfer_order_no'], 'required'],
            [['mall_id', 'user_id', 'is_pay'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_no', 'transfer_order_no', 'pay_type'], 'string', 'max' => 255],
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
            'order_no' => '提交微信或支付宝的订单号',
            'transfer_order_no' => '发起 打款的订单号',
            'amount' => '金额',
            'is_pay' => '支付状态 0--未支付|1--已支付',
            'pay_type' => '方式：wechat--微信打款 alipay--支付宝打款',
            'title' => 'Title',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
