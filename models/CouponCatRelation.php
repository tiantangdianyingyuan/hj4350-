<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%coupon_cat}}".
 *
 * @property int $id
 * @property int $coupon_id 优惠券
 * @property int $cat_id 分类
 * @property int $is_delete 删除
 */
class CouponCatRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_cat_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'cat_id'], 'required'],
            [['coupon_id', 'cat_id', 'is_delete'], 'integer'],
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
            'cat_id' => '分类',
            'is_delete' => '删除',
        ];
    }
}
