<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_card_relation}}".
 *
 * @property int $id
 * @property int $goods_id
 * @property int $card_id
 * @property int $num
 * @property int $is_delete
 * @property GoodsCards $goodsCards
 */
class GoodsCardRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_card_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'card_id'], 'required'],
            [['goods_id', 'card_id', 'num', 'is_delete'], 'integer'],
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
            'card_id' => 'Card ID',
            'num' => 'Num',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoodsCards()
    {
        return $this->hasOne(GoodsCards::className(), ['id' => 'card_id']);
    }
}