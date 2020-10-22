<?php

namespace app\plugins\scan_code_pay\models;

use app\models\MallMembers;
use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities_groups_level}}".
 *
 * @property int $id
 * @property int $group_id
 * @property int $level
 * @property int $is_delete
 * @property $member;
 */
class ScanCodePayActivitiesGroupsLevel extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities_groups_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'level'], 'required'],
            [['group_id', 'level', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'level' => 'Level',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMember()
    {
        return $this->hasOne(MallMembers::className(), ['level' => 'level'])->andWhere(['is_delete' => 0]);
    }
}
