<?php

namespace app\plugins\community\models;

use Yii;

/**
 * This is the model class for table "{{%community_middleman_activity}}".
 *
 * @property int $id
 * @property int $middleman_id 团长user_id
 * @property int $activity_id 活动id
 * @property int $is_remind 是否提醒 0--未提醒 1--已提醒
 * @property int $is_delete 是否删除
 */
class CommunityMiddlemanActivity extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_middleman_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['middleman_id', 'activity_id', 'is_remind', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'middleman_id' => '团长user_id',
            'activity_id' => '活动id',
            'is_remind' => '是否提醒 0--未提醒 1--已提醒',
            'is_delete' => '是否删除',
        ];
    }
}
