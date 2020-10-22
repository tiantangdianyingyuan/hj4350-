<?php

namespace app\plugins\flash_sale\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%flash_sale_goods_attr}}".
 *
 * @property int $id
 * @property string $discount 商品折扣
 * @property string $cut 商品减钱
 * @property int $type 1打折  2减钱  3促销价
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $is_delete
 */
class FlashSaleGoodsAttr extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%flash_sale_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discount', 'cut'], 'number'],
            [['goods_id', 'goods_attr_id'], 'required'],
            [['type', 'goods_id', 'goods_attr_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'discount' => 'Discount',
            'cut' => 'Cut',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
