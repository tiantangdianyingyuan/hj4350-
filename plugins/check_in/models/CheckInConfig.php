<?php

namespace app\plugins\check_in\models;

use Yii;

/**
 * This is the model class for table "{{%check_in_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $status 是否开启 0--关闭|1--开启
 * @property int $is_remind 是否提醒 0--关闭|1--开启
 * @property string $time 提醒时间
 * @property int $continue_type 连续签到周期1--不限|2--周清|3--月清
 * @property string $rule 签到规则
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class CheckInConfig extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'time', 'continue_type', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'is_remind', 'continue_type', 'is_delete'], 'integer'],
            [['time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['rule'], 'string'],
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
            'status' => '是否开启 0--关闭|1--开启',
            'is_remind' => '是否提醒 0--关闭|1--开启',
            'time' => '提醒时间',
            'continue_type' => '连续签到周期1--不限|2--周清|3--月清',
            'rule' => '签到规则',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
