<?php

namespace app\plugins\check_in\models;

use Yii;

/**
 * This is the model class for table "{{%check_in_user_remind}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $date
 * @property int $is_remind
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 */
class CheckInUserRemind extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_user_remind}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'date', 'is_remind', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'is_remind', 'is_delete'], 'integer'],
            [['date', 'created_at', 'deleted_at', 'updated_at'], 'safe'],
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
            'date' => 'Date',
            'is_remind' => 'Is Remind',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
