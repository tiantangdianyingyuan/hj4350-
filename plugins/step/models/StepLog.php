<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $step_id
 * @property int $type 1收入 2 支出
 * @property string $currency 活力币
 * @property string $remark 备注
 * @property string $data 详情
 * @property string $created_at
 */
class StepLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'step_id', 'type', 'created_at'], 'required'],
            [['mall_id', 'step_id', 'type'], 'integer'],
            [['currency'], 'number'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['remark'], 'string', 'max' => 255],
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
            'step_id' => 'Step ID',
            'type' => '1收入 2 支出',
            'currency' => '活力币',
            'remark' => '备注',
            'data' => '详情',
            'created_at' => 'Created At',
        ];
    }

    public function getStep()
    {
        return $this->hasOne(StepUser::className(), ['id' => 'step_id']);
    }
}
