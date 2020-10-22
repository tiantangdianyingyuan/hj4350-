<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mall_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $is_quick_shop 是否快速购买
 * @property int $is_sell_well 是否热销
 * @property int $is_negotiable 是否面议商品
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property GoodsWarehouse $goodsWarehouse
 * @property Goods $goods
 */
class MallGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mall_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'created_at'], 'required'],
            [['mall_id', 'goods_id', 'is_quick_shop', 'is_sell_well', 'is_negotiable', 'is_delete'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'is_quick_shop' => '是否快速购买',
            'is_sell_well' => '是否热销',
            'is_negotiable' => '是否面议商品',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('goods');
    }
}
