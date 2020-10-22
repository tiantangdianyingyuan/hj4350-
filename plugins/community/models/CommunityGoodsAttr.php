<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_goods_attr}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $attr_id
 * @property string $supply_price 供货价
 * @property int $is_delete
 */
class CommunityGoodsAttr extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_goods_attr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'attr_id', 'is_delete'], 'integer'],
            [['supply_price'], 'number'],
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
            'attr_id' => 'Attr ID',
            'supply_price' => '供货价',
            'is_delete' => 'Is Delete',
        ];
    }
}
