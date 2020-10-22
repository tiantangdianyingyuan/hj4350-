<?php

namespace app\plugins\scan_code_pay\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 活动名称
 * @property string $start_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property int $send_type 1.赠送所有规则|2.赠送满足最高规则
 * @property string $rules 买单规则
 * @property int $status 是否启用
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property $groups
 */
class ScanCodePayActivities extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'send_type', 'status', 'is_delete'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['rules'], 'string'],
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
            'name' => '活动名称',
            'start_time' => '活动开始时间',
            'end_time' => '活动结束时间',
            'send_type' => '1.赠送所有规则|2.赠送满足最高规则',
            'rules' => '买单规则',
            'status' => '是否启用',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGroups()
    {
        return $this->hasMany(ScanCodePayActivitiesGroups::className(), ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
