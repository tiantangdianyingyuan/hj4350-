<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_cat_relation}}".
 *
 * @property int $id
 * @property int $goods_warehouse_id
 * @property int $cat_id
 * @property int $is_delete
 * @property $goods
 */
class GoodsCatRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_cat_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'goods_warehouse_id', 'cat_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_warehouse_id' => 'Goods Warhouse ID',
            'cat_id' => 'Cat ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(), ['goods_warehouse_id' => 'goods_warehouse_id'])
            ->andWhere(['is_delete' => 0]);
    }
}
