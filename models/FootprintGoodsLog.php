<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%footprint_goods_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class FootprintGoodsLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%footprint_goods_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'mall_id', 'user_id', 'goods_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
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
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::class, ['goods_id' => 'goods_id'])->andWhere(['is_delete' => 0]);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id']);
    }

    public function getMallGoods()
    {
        return $this->hasOne(MallGoods::className(), ['goods_id' => 'id']);
    }
}
