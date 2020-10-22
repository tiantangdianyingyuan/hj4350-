<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_coupon_center}}".
 *
 * @property int $id
 * @property int $mall_id 商城ID
 * @property int $user_coupon_id 优惠券ID
 * @property int $user_id 用户ID
 * @property int $is_delete 是否删除 0--不删除 1--删除
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class UserCouponCenter extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon_center}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_coupon_id', 'user_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
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
            'mall_id' => '商城ID',
            'user_coupon_id' => '优惠券ID',
            'user_id' => '用户ID',
            'is_delete' => '是否删除 0--不删除 1--删除',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'deleted_at' => '删除时间',
        ];
    }

}
