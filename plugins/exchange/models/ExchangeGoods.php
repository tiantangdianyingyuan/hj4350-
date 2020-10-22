<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%exchange_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $library_id
 * @property int $goods_id
 * @property string $created_at
 * @property string $updated_at
 */
class ExchangeGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id'], 'required'],
            [['mall_id', 'library_id', 'goods_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'library_id' => 'Library ID',
            'goods_id' => 'Goods ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getLibrary()
    {
        return $this->hasOne(ExchangeLibrary::className(), ['id' => 'library_id']);
    }
}
