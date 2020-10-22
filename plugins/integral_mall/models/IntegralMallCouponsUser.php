<?php

namespace app\plugins\integral_mall\models;

use app\models\User;
use app\models\UserCoupon;
use Yii;

/**
 * This is the model class for table "{{%integral_mall_coupons_user}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $mall_id
 * @property int $integral_mall_coupon_id 积分商城优惠券ID
 * @property int $user_coupon_id 关联用户优惠券ID
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property UserCoupon $userCoupon
 */
class IntegralMallCouponsUser extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_coupons_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'integral_mall_coupon_id', 'user_coupon_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'mall_id', 'integral_mall_coupon_id', 'user_coupon_id', 'is_delete'], 'integer'],
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
            'user_id' => '用户ID',
            'mall_id' => 'Mall ID',
            'integral_mall_coupon_id' => '积分商城优惠券ID',
            'user_coupon_id' => '关联用户优惠券ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getIntegralMallCoupon()
    {
        return $this->hasOne(IntegralMallCoupons::className(), ['id' => 'integral_mall_coupon_id']);
    }

    public function getUserCoupon()
    {
        return $this->hasOne(UserCoupon::className(), ['id' => 'user_coupon_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
