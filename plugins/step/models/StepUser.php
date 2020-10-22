<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;
use app\models\User;

/**
 * This is the model class for table "{{%step_user}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id 用户ID
 * @property int $ratio 概率加成
 * @property string $step_currency
 * @property int $parent_id 邀请ID
 * @property int $invite_ratio 邀请好友加成
 * @property int $is_remind 是否提醒
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class StepUser extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'step_currency', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'ratio', 'parent_id', 'invite_ratio', 'is_remind', 'is_delete'], 'integer'],
            [['step_currency'], 'number'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'user_id' => '用户ID',
            'ratio' => '概率加成',
            'step_currency' => 'Step Currency',
            'parent_id' => '邀请ID',
            'invite_ratio' => '邀请好友加成',
            'is_remind' => '是否提醒',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getParent()
    {
        return $this->hasOne(User::className(), ['id' => 'parent_id']);
    }
}
