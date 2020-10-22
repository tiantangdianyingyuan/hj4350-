<?php

namespace app\plugins\diy\models;


use app\models\UserCoupon;

/**
 * This is the model class for table "{{%diy_coupon_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $template_id
 * @property int $is_delete
 * @property int $user_coupon_id
 * @property string $created_at
 * @property string $raffled_at
 * @property string $deleted_at
 */
class DiyCouponLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diy_coupon_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'template_id', 'user_coupon_id'], 'required'],
            [['mall_id', 'user_id', 'template_id', 'is_delete', 'user_coupon_id'], 'integer'],
            [['created_at', 'raffled_at', 'deleted_at'], 'safe'],
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
            'template_id' => 'Template ID',
            'user_coupon_id' => 'User Coupon ID',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'raffled_at' => 'Raffled At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getUserCoupon()
    {
        return $this->hasOne(UserCoupon::className(), ['id' => 'user_coupon_id']);
    }
}
