<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%exchange_coupon_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $code_id
 * @property int $user_coupon_id
 * @property string $created_at
 */
class ExchangeCouponRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_coupon_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'code_id', 'user_coupon_id'], 'required'],
            [['mall_id', 'code_id', 'user_coupon_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'code_id' => 'Code ID',
            'user_coupon_id' => 'User Coupon ID',
            'created_at' => 'Created At',
        ];
    }
}
