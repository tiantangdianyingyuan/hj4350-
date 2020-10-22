<?php

namespace app\plugins\check_in\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%check_in_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $total 累计签到时间
 * @property int $continue 连续签到时间
 * @property int $is_remind 是否开启签到提醒
 * @property string $created_at
 * @property int $is_delete
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $continue_start 连续签到的起始日期
 * @property User $user
 */
class CheckInUser extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'is_delete', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'total', 'continue', 'is_remind', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'continue_start'], 'safe'],
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
            'total' => '累计签到时间',
            'continue' => '连续签到时间',
            'is_remind' => '是否开启签到提醒',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'continue_start' => '连续签到的起始日期',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
