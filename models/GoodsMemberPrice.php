<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_member_price}}".
 *
 * @property int $id
 * @property int $level
 * @property string $price
 * @property int $goods_attr_id
 * @property int $is_delete
 * @property int $goods_id
 */
class GoodsMemberPrice extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_member_price}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level', 'goods_attr_id', 'goods_id'], 'required'],
            [['level', 'goods_attr_id', 'is_delete', 'goods_id'], 'integer'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => 'Level',
            'price' => 'Price',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
            'goods_id' => 'Goods ID',
        ];
    }
}
