<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;
use app\models\GoodsAttr;

/**
 * This is the model class for table "{{%step_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $currency 活力币
 * @property int $goods_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Goods $goods
 */
class StepGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'goods_id', 'is_delete'], 'integer'],
            [['currency'], 'number'],
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
            'currency' => '活力币',
            'goods_id' => 'Goods ID',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getGoodsAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }
}
