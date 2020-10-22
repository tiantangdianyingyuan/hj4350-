<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_activity}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $title
 * @property string $currency 奖金池
 * @property int $step_num 挑战步数
 * @property string $bail_currency 保证金
 * @property string $status
 * @property int $type 0进行中 1 已完成 2 已解散
 * @property string $begin_at 开始时间
 * @property string $end_at 结束时间
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class StepActivity extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'title', 'status', 'begin_at', 'end_at', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'step_num', 'type', 'is_delete', 'status'], 'integer'],
            [['currency', 'bail_currency'], 'number'],
            [['begin_at', 'end_at', 'created_at', 'deleted_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
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
            'title' => 'Title',
            'currency' => '奖金池',
            'step_num' => '挑战步数',
            'bail_currency' => '保证金',
            'status' => 'Status',
            'type' => '0进行中 1 已完成 2 已解散',
            'begin_at' => '开始时间',
            'end_at' => '结束时间',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
        
    public function getLog()
    {
        return $this->hasOne(StepActivityLog::className(), ['activity_id' => 'id']);
    }
}
