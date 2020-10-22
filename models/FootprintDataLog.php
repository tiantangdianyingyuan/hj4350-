<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%footprint_data_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $key
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $statistics_time 上一次统计的时间
 */
class FootprintDataLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%footprint_data_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'key', 'value'], 'required'],
            [['mall_id', 'user_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'statistics_time'], 'safe'],
            [['key', 'value'], 'string', 'max' => 60],
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
            'key' => 'Key',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'statistics_time' => '上一次统计的时间',
        ];
    }
}
