<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%assistant_data}}".
 *
 * @property int $id
 * @property int $type 类型 0--淘宝 1--淘宝app
 * @property string $itemId 原始商品id
 * @property string $json 数据
 * @property string $created_at
 */
class AssistantData extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%assistant_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['json', 'created_at'], 'required'],
            [['json'], 'string'],
            [['created_at'], 'safe'],
            [['itemId'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型 0--淘宝 1--淘宝app',
            'itemId' => '原始商品id',
            'json' => '数据',
            'created_at' => 'Created At',
        ];
    }
}
