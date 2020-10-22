<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_center}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $coupon_id 优惠券id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class CouponCenter extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_center}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'coupon_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'coupon_id', 'is_delete'], 'integer'],
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
            'coupon_id' => '优惠券id',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
