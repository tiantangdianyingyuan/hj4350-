<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_service_relation}}".
 *
 * @property int $id
 * @property int $service_id
 * @property int $goods_id
 * @property int $is_delete
 */
class GoodsServiceRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_service_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_id', 'goods_id'], 'required'],
            [['service_id', 'goods_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service ID',
            'goods_id' => 'Goods ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
