<?php

namespace app\plugins\pond\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%pond_log_coupon_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_coupon_id
 * @property int $pond_log_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class PondLogCouponRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond_log_coupon_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_coupon_id', 'pond_log_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_coupon_id', 'pond_log_id', 'is_delete'], 'integer'],
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
            'pond_log_id' => '奖品记录id',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
