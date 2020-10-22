<?php

namespace app\plugins\scratch\models;

use app\models\Goods;
use app\models\User;
use app\models\UserCoupon;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%scratch_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $scratch_id
 * @property int $user_id
 * @property int $status  0预领取 1 未领取 2 已领取
 * @property int $type 1.红包2.优惠券3.积分4.实物
 * @property int $num 积分数量
 * @property string $detail 优惠券详情
 * @property string $goods_id 赠品
 * @property string $price 价格
 * @property int $order_id
 * @property string $raffled_at
 * @property string $created_at
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $token 订单token
 * @property Goods $goods
 */
class ScratchLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scratch_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'scratch_id', 'user_id', 'created_at'], 'required'],
            [['mall_id', 'scratch_id', 'user_id', 'status', 'type', 'num', 'order_id', 'goods_id', 'is_delete'], 'integer'],
            [['detail'], 'string'],
            [['price'], 'number'],
            [['raffled_at', 'created_at', 'deleted_at'], 'safe'],
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
            'scratch_id' => 'Scratch ID',
            'user_id' => 'User ID',
            'status' => ' 0预领取 1 未领取 2 已领取',
            'type' => '1.红包2.优惠券3.积分4.实物',
            'num' => '积分数量',
            'detail' => '详情',
            'goods_id' => '赠品',
            'price' => '价格',
            'order_id' => 'Order ID',
            'raffled_at' => 'Raffled At',
            'created_at' => 'Created At',
            'is_delete' => '删除',
            'deleted_at' => 'Deleted At',
            'token' => '订单token',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->alias('u');
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getCoupon()
    {
        return $this->hasOne(UserCoupon::className(), ['id' => 'user_coupon_id'])
            ->viaTable(ScratchLogCouponRelation::tableName(), ['scratch_log_id' => 'id']);
    }
}
