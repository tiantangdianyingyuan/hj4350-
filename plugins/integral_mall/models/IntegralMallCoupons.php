<?php

namespace app\plugins\integral_mall\models;

use app\models\Coupon;
use Yii;

/**
 * This is the model class for table "{{%integral_mall_coupons}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $coupon_id
 * @property int $exchange_num 兑换次数0.不限制
 * @property int $integral_num 所需兑换积分
 * @property int $send_count 发放优惠券总数
 * @property string $price 价格
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property $couponOrders
 * @property Coupon $coupon
 */
class IntegralMallCoupons extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_coupons}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'coupon_id', 'send_count', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'coupon_id', 'exchange_num', 'integral_num', 'send_count', 'is_delete'], 'integer'],
            [['price'], 'number'],
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
            'coupon_id' => 'Coupon ID',
            'exchange_num' => '兑换次数0.不限制',
            'integral_num' => '所需兑换积分',
            'send_count' => '发放优惠券总数',
            'price' => '价格',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }

    public function getCouponOrders()
    {
        return $this->hasMany(IntegralMallCouponsOrders::className(), ['integral_mall_coupon_id' => 'id'])
            ->where(['user_id' => Yii::$app->user->id, 'is_pay' => 1]);
    }
}
