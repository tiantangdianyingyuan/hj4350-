<?php

namespace app\plugins\scan_code_pay\models;

use app\models\MallMembers;
use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_activities_groups}}".
 *
 * @property int $id
 * @property int $activity_id
 * @property int $is_delete
 * @property string $name
 * @property $members
 * @property $rules
 * @property $scanMembers
 */
class ScanCodePayActivitiesGroups extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_activities_groups}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['activity_id', 'is_delete'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'activity_id' => '活动ID',
            'is_delete' => 'Is Delete'
        ];
    }

    public function getMembers()
    {
        return $this->hasMany(MallMembers::className(), ['level' => 'level'])
            ->viaTable(ScanCodePayActivitiesGroupsLevel::tableName(), ['group_id' => 'id'], function ($query) {
                $query->andWhere(['is_delete' => 0]);
            })->andWhere(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id]);
    }

    public function getScanMembers()
    {
        return $this->hasMany(ScanCodePayActivitiesGroupsLevel::className(), ['group_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getRules()
    {
        return $this->hasMany(ScanCodePayActivitiesGroupsRules::className(), ['group_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
