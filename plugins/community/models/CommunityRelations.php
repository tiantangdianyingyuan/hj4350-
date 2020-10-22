<?php

namespace app\plugins\community\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%community_relations}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $middleman_id
 * @property int $is_delete
 * @property User $user
 */
class CommunityRelations extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%community_relations}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'middleman_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'middleman_id' => 'Middleman ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
