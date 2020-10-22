<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_goods_relation}}".
 *
 * @property int $id
 * @property int $coupon_id 优惠券
 * @property int $goods_warehouse_id 商品
 * @property int $is_delete 删除
 */
class CouponGoodsRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_goods_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'goods_warehouse_id'], 'required'],
            [['coupon_id', 'goods_warehouse_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coupon_id' => '优惠券',
            'goods_warehouse_id' => '商品',
            'is_delete' => '删除',
        ];
    }
}
