<?php

namespace app\plugins\pintuan\models;

use app\models\MallMembers;
use Yii;

/**
 * This is the model class for table "{{%pintuan_goods_member_price}}".
 *
 * @property int $id
 * @property int $level
 * @property string $price
 * @property int $goods_id 商城商品ID
 * @property int $goods_attr_id 商城商品规格ID
 * @property int $pintuan_goods_groups_id 拼团设置ID
 * @property int $pintuan_goods_attr_id 拼团商品规格ID
 * @property int $is_delete
 */
class PintuanGoodsMemberPrice extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pintuan_goods_member_price}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level', 'goods_id', 'goods_attr_id', 'pintuan_goods_groups_id', 'pintuan_goods_attr_id'], 'required'],
            [['level', 'goods_id', 'goods_attr_id', 'pintuan_goods_groups_id', 'pintuan_goods_attr_id', 'is_delete'], 'integer'],
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
            'goods_id' => '商城商品ID',
            'goods_attr_id' => '商城商品规格ID',
            'pintuan_goods_groups_id' => '拼团设置ID',
            'pintuan_goods_attr_id' => '拼团商品规格ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
