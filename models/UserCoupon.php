<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_coupon}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id 用户
 * @property int $coupon_id 优惠券
 * @property string $sub_price 满减
 * @property string $discount 折扣
 * @property string $discount_limit 折扣优惠上限
 * @property string $coupon_min_price 最低消费金额
 * @property int $type 优惠券类型：1=折扣，2=满减
 * @property string $start_time 有效期开始时间
 * @property string $end_time 有效期结束时间
 * @property int $is_use 是否已使用：0=未使用，1=已使用
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $receive_type 获取方式
 * @property string $coupon_data 优惠券信息json格式
 * @property Coupon $coupon
 */
class UserCoupon extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'coupon_id', 'coupon_min_price', 'created_at', 'updated_at', 'deleted_at', 'coupon_data'], 'required'],
            [['mall_id', 'user_id', 'coupon_id', 'type', 'is_use', 'is_delete'], 'integer'],
            [['sub_price', 'discount', 'coupon_min_price', 'discount_limit'], 'number'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['coupon_data'], 'string'],
            [['receive_type'], 'string', 'max' => 255],
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
            'user_id' => '用户',
            'coupon_id' => '优惠券',
            'sub_price' => '满减',
            'discount' => '折扣',
            'discount_limit' => '折扣优惠上限',
            'coupon_min_price' => '最低消费金额',
            'type' => '优惠券类型：1=折扣，2=满减',
            'start_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'is_use' => '是否已使用：0=未使用，1=已使用',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'receive_type' => '获取方式',
            'coupon_data' => '优惠券信息json格式',
        ];
    }

    public function getAuto()
    {
        return $this->hasOne(UserCouponAuto::className(), ['user_coupon_id' => 'id']);
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
