<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_coupon_relation}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $coupon_id
 * @property int $num
 * @property int $is_delete
 * @property Coupon $goodsCoupons
 */
class GoodsCouponRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_coupon_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'coupon_id'], 'required'],
            [['goods_id', 'coupon_id', 'num', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'coupon_id' => 'Coupon ID',
            'num' => 'Num',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoodsCoupons()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
