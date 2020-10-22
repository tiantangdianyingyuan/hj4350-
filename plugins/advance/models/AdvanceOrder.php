<?php

namespace app\plugins\advance\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%advance_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id 商品ID
 * @property int $goods_attr_id 规格ID
 * @property int $goods_num
 * @property int $order_id
 * @property string $order_no
 * @property string $advance_no 定金订单号
 * @property string $deposit 定金
 * @property string $swell_deposit 膨胀金
 * @property int $is_cancel 1取消
 * @property string $cancel_time
 * @property int $is_refund 1退款
 * @property int $is_delete 1删除
 * @property int $is_pay 是否支付：0.未支付|1.已支付
 * @property int $is_recycle 是否加入回收站 0.否|1.是
 * @property int $pay_type 支付方式：1.在线支付 2.货到付款 3.余额支付
 * @property string $pay_time
 * @property string $remark 备注
 * @property string $auto_cancel_time 自动取消时间
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $goods_info
 * @property string $token
 * @property string $order_token
 * @property string $preferential_price
 * @property User $user
 */
class AdvanceOrder extends \app\models\ModelActiveRecord
{
    /**
     *  强制取消定金订单事件
     */
    const EVENT_REFUND = 'depositOrderRefund';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'goods_attr_id', 'advance_no', 'goods_info', 'token'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'goods_attr_id', 'goods_num', 'order_id', 'is_cancel', 'is_refund', 'is_delete', 'is_pay', 'is_recycle', 'pay_type'], 'integer'],
            [['deposit', 'swell_deposit', 'preferential_price'], 'number'],
            [['cancel_time', 'pay_time', 'auto_cancel_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['goods_info'], 'string'],
            [['order_no', 'advance_no', 'remark'], 'string', 'max' => 255],
            [['token', 'order_token'], 'string', 'max' => 32],
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
            'goods_id' => '商品ID',
            'goods_attr_id' => '规格ID',
            'goods_num' => 'Goods Num',
            'order_id' => 'Order ID',
            'order_no' => 'Order No',
            'advance_no' => '定金订单号',
            'deposit' => '定金',
            'swell_deposit' => '膨胀金',
            'is_cancel' => '1取消',
            'cancel_time' => 'Cancel Time',
            'is_refund' => '1退款',
            'is_delete' => '1删除',
            'is_pay' => '是否支付：0.未支付|1.已支付',
            'is_recycle' => '是否加入回收站 0.否|1.是',
            'pay_type' => '支付方式：1.在线支付 2.货到付款 3.余额支付',
            'pay_time' => 'Pay Time',
            'remark' => '备注',
            'auto_cancel_time' => '自动取消时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'goods_info' => 'Goods Info',
            'token' => 'Token',
            'order_token' => 'Order Token',
            'preferential_price' => '活动优惠金额',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(\app\models\GoodsAttr::className(), ['id' => 'goods_attr_id']);
    }

    public function getAttr()
    {
        return $this->hasMany(\app\models\GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }
}
