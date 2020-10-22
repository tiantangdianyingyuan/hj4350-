<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_member_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $coupon_id
 * @property int $member_level
 * @property string $created_at
 * @property string $deleted_at
 */
class CouponMemberRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_member_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'coupon_id', 'member_level', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'coupon_id', 'member_level'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'member_level' => 'Member Level',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At'
        ];
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
