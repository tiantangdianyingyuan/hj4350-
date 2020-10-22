<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_ad_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $ad_id 删除
 * @property int $is_delete
 * @property string $created_at
 * @property string $raffled_at
 * @property string $deleted_at
 */
class StepAdLog extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_ad_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'ad_id', 'is_delete'], 'integer'],
            [['created_at', 'raffled_at', 'deleted_at'], 'safe'],
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
            'user_id' => 'User ID',
            'ad_id' => '删除',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'raffled_at—' => 'Raffled At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
