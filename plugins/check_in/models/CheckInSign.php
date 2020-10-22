<?php

namespace app\plugins\check_in\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%check_in_sign}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $number 签到奖励数量
 * @property string $type 签到奖励类型integral--积分|balance--余额
 * @property int $day 签到天数
 * @property int $status 0--普通签到奖励 1--连续签到奖励 2--累计签到奖励
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $token
 * @property int $award_id 签到奖励id
 */
class CheckInSign extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_sign}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at', 'token'], 'required'],
            [['mall_id', 'user_id', 'day', 'status', 'is_delete', 'award_id'], 'integer'],
            [['number'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type', 'token'], 'string', 'max' => 255],
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
            'number' => '签到奖励数量',
            'type' => '签到奖励类型integral--积分|balance--余额',
            'day' => '签到天数',
            'status' => '1--普通签到奖励 2--连续签到奖励 3--累计签到奖励',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'token' => 'Token',
            'award_id' => '签到奖励id',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
