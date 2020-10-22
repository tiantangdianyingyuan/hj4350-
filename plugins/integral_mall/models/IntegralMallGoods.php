<?php

namespace app\plugins\integral_mall\models;

use Yii;

/**
 * This is the model class for table "{{%integral_mall_goods}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $mall_id
 * @property int $integral_num
 * @property int $is_home 放置首页0.否|1.是
 * @property int $is_delete
 * @property GoodsAttr[] $attr
 * @property Goods $goods
 */
class IntegralMallGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'is_home', 'is_delete', 'mall_id', 'integral_num'], 'integer'],
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
            'integral_num' => '商品积分',
            'is_home' => '放置首页0.否|1.是',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }
}
