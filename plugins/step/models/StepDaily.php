<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_daily}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $step_id
 * @property int $ratio 兑换概率
 * @property int $step_log 日志关联
 * @property int $num 兑换加成后数量
 * @property string $created_at
 */
class StepDaily extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_daily}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'step_id', 'ratio', 'num', 'created_at'], 'required'],
            [['mall_id', 'step_id', 'ratio', 'num'], 'integer'],
            [['created_at'], 'safe'],
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
            'ratio' => '兑换概率',
            'num' => '兑换加成后数量',
            'created_at' => 'Created At',
        ];
    }

    public function getStep()
    {
        return $this->hasOne(StepUser::className(), ['id' => 'step_id']);
    }
}
