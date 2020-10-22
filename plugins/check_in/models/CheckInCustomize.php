<?php

namespace app\plugins\check_in\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%check_in_customize}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class CheckInCustomize extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_customize}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'value', 'created_at', 'updated_at'], 'required'],
            [['mall_id'], 'integer'],
            [['value'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
