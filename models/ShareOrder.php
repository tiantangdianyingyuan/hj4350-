<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%share_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $user_id 购物者用户id
 * @property int $first_parent_id 上一级用户id
 * @property int $second_parent_id 上二级用户id
 * @property int $third_parent_id 上三级用户id
 * @property string $first_price 上一级分销佣金
 * @property string $second_price 上二级分销佣金
 * @property string $third_price 上三级分销佣金
 * @property int $is_transfer 佣金发放状态：0=未发放，1=已发放
 * @property int $is_refund 是否退款：0=未退款，1=已退款
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $price 用于分销的金额
 * @property int $first_share_type 一级分销的分销类型
 * @property string $first_share_price 一级佣金
 * @property int $second_share_type 二级分销的分销类型
 * @property string $second_share_price 二级佣金
 * @property int $third_share_type 三级分销的分销类型
 * @property string $third_share_price 三级佣金
 * @property int $flag 修改记录 0--售后优化之前的分销订单 1--售后优化之后的订单
 * @property Order $order
 * @property User $user
 * @property OrderDetail $orderDetail
 */
class ShareOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%share_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'user_id'], 'required'],
            [['mall_id', 'order_id', 'order_detail_id', 'user_id', 'first_parent_id', 'second_parent_id', 'third_parent_id', 'is_refund', 'is_transfer', 'is_delete', 'first_share_type', 'second_share_type', 'third_share_type', 'flag'], 'integer'],
            [['first_price', 'second_price', 'third_price', 'price', 'first_share_price', 'second_share_price', 'third_share_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'user_id' => '购物者用户id',
            'first_parent_id' => '上一级用户id',
            'second_parent_id' => '上二级用户id',
            'third_parent_id' => '上三级用户id',
            'first_price' => '上一级分销佣金',
            'second_price' => '上二级分销佣金',
            'third_price' => '上三级分销佣金',
            'is_refund' => '是否退款',
            'is_transfer' => '佣金发放状态：0=未发放，1=已发放',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'price' => '用于分销的金额',
            'first_share_type' => '一级分销的分销类型',
            'first_share_price' => '一级佣金',
            'second_share_type' => '二级分销的分销类型',
            'second_share_price' => '二级佣金',
            'third_share_type' => '三级分销的分销类型',
            'third_share_price' => '三级佣金',
            'flag' => '修改记录 0--售后优化之前的分销订单 1--售后优化之后的订单',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrderDetail()
    {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }
}
