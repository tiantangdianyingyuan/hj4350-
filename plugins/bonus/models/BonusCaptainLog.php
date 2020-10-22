<?php

namespace app\plugins\bonus\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_captain_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $handler 操作人
 * @property int $user_id 队长
 * @property string $event 事件名
 * @property string $content 记录信息
 * @property string $create_at
 * @property int $is_delete
 */
class BonusCaptainLog extends \yii\db\ActiveRecord
{
    //后台审核成为队长
    const BECOME_CAPTAIN = 'bonus_become_captain';
    //后台审核拒绝
    const REJECT_CAPTAIN = 'bonus_reject_captain';
    //后台移除队长
    const REMOVE_CAPTAIN = 'bonus_remove_captain';
    //成为分销商
    const BECOME_SHARE_AFFECT = 'bonus_become_share_affect_captain';
    //后台更改用户上级
    const CHANGE_PARENT = 'bonus_change_parent';
    //记录异常
    const BONUS_EXCEPTION = 'bonus_captain_exception';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_captain_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'event', 'content',], 'required'],
            [['mall_id', 'handler', 'user_id', 'is_delete'], 'integer'],
            [['content'], 'string'],
            [['create_at'], 'safe'],
            [['event'], 'string', 'max' => 255],
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
            'handler' => 'Handler',
            'user_id' => 'User ID',
            'event' => 'Event',
            'content' => 'Content',
            'create_at' => 'Create At',
            'is_delete' => 'Is Delete',
        ];
    }
}
