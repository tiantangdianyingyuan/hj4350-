<?php

namespace app\plugins\scratch\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%scratch_log_coupon_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_coupon_id 用户优惠券id
 * @property int $scratch_log_id 记录id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class ScratchLogCouponRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scratch_log_coupon_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_coupon_id', 'scratch_log_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_coupon_id', 'scratch_log_id', 'is_delete'], 'integer'],
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
            'user_coupon_id' => '用户优惠券id',
            'scratch_log_id' => '记录id',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
