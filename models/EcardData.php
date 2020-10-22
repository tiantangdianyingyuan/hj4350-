<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ecard_data}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $ecard_id
 * @property string $token
 * @property string $key
 * @property string $value
 * @property int $is_delete
 */
class EcardData extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ecard_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'ecard_id', 'token', 'key', 'value', 'is_delete'], 'required'],
            [['mall_id', 'ecard_id', 'is_delete'], 'integer'],
            [['value'], 'string'],
            [['token', 'key'], 'string', 'max' => 255],
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
            'ecard_id' => 'Ecard ID',
            'token' => 'Token',
            'key' => 'Key',
            'value' => 'Value',
            'is_delete' => 'Is Delete',
        ];
    }
}
