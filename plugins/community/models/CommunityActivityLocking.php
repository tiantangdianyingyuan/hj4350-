<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_activity_locking}}".
 *
 * @property int $id
 * @property int $activity_id
 * @property int $middleman_id
 * @property int $is_delete
 */
class CommunityActivityLocking extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_activity_locking}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'middleman_id', 'is_delete'], 'integer'],
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
            'is_delete' => 'Is Delete',
        ];
    }
}
