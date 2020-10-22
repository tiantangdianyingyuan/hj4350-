<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_activity_info}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $activity_log_id a
 * @property int $num 提交步数
 * @property string $open_date 创建时间
 * @property string $created_at
 */
class StepActivityInfo extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_activity_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'activity_log_id', 'num', 'created_at'], 'required'],
            [['mall_id', 'activity_log_id', 'num'], 'integer'],
            [['created_at'], 'safe'],
            [['open_date'], 'string', 'max' => 11],
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
            'activity_log_id' => 'a',
            'num' => '提交步数',
            'open_date' => '创建时间',
            'created_at' => 'Created At',
        ];
    }
}
