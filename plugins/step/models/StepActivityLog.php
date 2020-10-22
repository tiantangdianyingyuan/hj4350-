<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_activity_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $step_id
 * @property int $activity_id
 * @property int $status 0报名  1成功 2失败
 * @property string $created_at
 * @property string $raffled_at
 * @property string $step_currency
 * reward_currency
 */
class StepActivityLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_activity_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'step_id', 'activity_id', 'created_at', 'raffled_at'], 'required'],
            [['mall_id', 'step_id', 'activity_id', 'status'], 'integer'],
            [['step_currency', 'reward_currency'], 'number'],
            [['created_at', 'raffled_at'], 'safe'],
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
            'activity_id' => 'Activity ID',
            'status' => '0报名  1成功 2失败',
            'step_currency' => '报名费',
            'reward_currency'=> '奖励',
            'created_at' => 'Created At',
            'raffled_at' => 'Raffled At',
        ];
    }
    public function getStep()
    {
        return $this->hasOne(StepUser::className(), ['id' => 'step_id']);
    }
    
    public function getActivity()
    {
        return $this->hasOne(StepActivity::className(), ['id' => 'activity_id']);
    }
}
