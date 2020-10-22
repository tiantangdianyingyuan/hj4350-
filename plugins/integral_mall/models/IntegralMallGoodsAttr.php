<?php

namespace app\plugins\integral_mall\models;

use Yii;

/**
 * This is the model class for table "{{%integral_mall_goods_attr}}".
 *
 * @property int $id
 * @property int $integral_num 商品所需积分
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $is_delete
 */
class IntegralMallGoodsAttr extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['integral_num', 'goods_id', 'goods_attr_id', 'is_delete'], 'integer'],
            [['goods_id', 'goods_attr_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'integral_num' => '商品所需积分',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
