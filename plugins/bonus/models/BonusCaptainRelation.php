<?php

namespace app\plugins\bonus\models;

use app\models\User;
use app\models\UserInfo;
use Yii;

/**
 * This is the model class for table "{{%bonus_captain_relation}}".
 *
 * @property int $id
 * @property int $captain_id 队长id
 * @property int $user_id 团队id
 * @property int $is_delete
 */
class BonusCaptainRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bonus_captain_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['captain_id', 'user_id'], 'required'],
            [['id', 'captain_id', 'user_id', 'is_delete'], 'integer'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'captain_id' => '队长id',
            'user_id' => '团员id',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
