<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_mall_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_coupon_id
 * @property int $is_delete 删除
 */
class CouponMallRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_mall_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_coupon_id'], 'required'],
            [['mall_id', 'user_coupon_id', 'is_delete'], 'integer'],
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
            'user_coupon_id' => 'User Coupon ID',
            'is_delete' => '删除',
        ];
    }
}
