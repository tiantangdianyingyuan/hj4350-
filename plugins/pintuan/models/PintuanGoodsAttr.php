<?php

namespace app\plugins\pintuan\models;

use app\models\GoodsAttr;
use Yii;

/**
 * This is the model class for table "{{%pintuan_goods_attr}}".
 *
 * @property int $id
 * @property string $pintuan_price 拼团价
 * @property int $pintuan_stock 拼团库存
 * @property int $pintuan_goods_groups_id 拼团设置ID
 * @property int $goods_attr_id 商城商品规格ID
 * @property int $goods_id 商城商品ID
 * @property int $is_delete
 * @property GoodsAttr $goodsAttr
 * @property PintuanGoodsShare $share
 * @property PintuanGoodsMemberPrice[] $memberPrice
 * @property PintuanGoodsShare[] $shareLevel
 */
class PintuanGoodsAttr extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pintuan_price'], 'number'],
            [['pintuan_stock', 'pintuan_goods_groups_id', 'goods_attr_id', 'goods_id'], 'required'],
            [['pintuan_stock', 'pintuan_goods_groups_id', 'goods_attr_id', 'goods_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pintuan_price' => '拼团价',
            'pintuan_stock' => '拼团库存',
            'pintuan_goods_groups_id' => '拼团设置ID',
            'goods_attr_id' => '商城商品规格ID',
            'goods_id' => '商城商品ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getShare()
    {
        return $this->hasOne(PintuanGoodsShare::className(), ['pintuan_goods_attr_id' => 'id'])
            ->andWhere(['is_delete' => 0, 'level' => 0]);
    }

    public function getMemberPrice()
    {
        return $this->hasMany(PintuanGoodsMemberPrice::className(), ['pintuan_goods_attr_id' => 'id'])
            ->andWhere(['is_delete' => 0]);
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'goods_attr_id'])
            ->andWhere(['is_delete' => 0]);
    }

    public function getShareLevel()
    {
        return $this->hasMany(PintuanGoodsShare::className(), ['pintuan_goods_attr_id' => 'id'])
            ->where(['is_delete' => 0]);
    }
}
