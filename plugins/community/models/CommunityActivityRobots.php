<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_activity_robots}}".
 *
 * @property int $id
 * @property int $activity_id
 * @property int $middleman_id
 * @property string $robots_ids
 * @property int $is_delete
 */
class CommunityActivityRobots extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_activity_robots}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'middleman_id', 'is_delete'], 'integer'],
            [['robots_ids'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'middleman_id' => 'Middleman ID',
            'robots_ids' => 'Robots Ids',
            'is_delete' => 'Is Delete',
        ];
    }
}
