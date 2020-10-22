<?php

namespace app\models;

use Yii;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%live_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $audit_id
 */
class LiveGoods extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%live_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'audit_id'], 'required'],
            [['mall_id', 'goods_id', 'audit_id'], 'integer'],
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
            'audit_id' => 'Audit ID',
        ];
    }
}
