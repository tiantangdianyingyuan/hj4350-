<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_coupon_member}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $member_level 会员等级
 * @property int $user_coupon_id
 * @property int $is_delete
 */
class UserCouponMember extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon_member}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'user_coupon_id', 'is_delete'], 'required'],
            [['mall_id', 'user_id', 'member_level', 'user_coupon_id', 'is_delete'], 'integer'],
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
            'member_level' => '会员等级',
            'user_coupon_id' => 'User Coupon ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
