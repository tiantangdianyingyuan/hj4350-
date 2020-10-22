<?php

namespace app\plugins\pond\models;

use app\models\Goods;
use app\models\ModelActiveRecord;
use app\models\User;
use app\models\UserCoupon;

/**
 * This is the model class for table "{{%pond_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $pond_id
 * @property int $user_id
 * @property int $status  0未领取1 已领取
 * @property int $type 1.红包2.优惠券3.积分4.实物5无
 * @property int $num 积分数量
 * @property string $detail 详情
 * @property string $price 价格
 * @property int $order_id
 * @property int $goods_id
 * @property string $raffled_at
 * @property string $created_at
 * @property string $token 订单表的token
 * @property Goods $goods
 */
class PondLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'pond_id', 'user_id', 'status', 'type', 'num', 'order_id', 'goods_id'], 'integer'],
            [['pond_id', 'user_id', 'status', 'type', 'created_at'], 'required'],
            [['price'], 'number'],
            [['raffled_at', 'created_at'], 'safe'],
            [['detail'], 'string', 'max' => 2000],
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
            'mall_id' => 'Store ID',
            'pond_id' => 'Pond ID',
            'user_id' => 'User ID',
            'status' => ' 0未领取1 已领取',
            'type' => '1.红包2.优惠券3.积分4.实物5无',
            'num' => '积分数量',
            'detail' => '赠品',
            'price' => '价格',
            'goods_id' => '商品id',
            'order_id' => 'Order ID',
            'raffled_at' => 'Raffled At',
            'created_at' => 'Created At',
            'token' => '订单表的token',
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
            ->viaTable(PondLogCouponRelation::tableName(), ['pond_log_id' => 'id']);
    }
}
