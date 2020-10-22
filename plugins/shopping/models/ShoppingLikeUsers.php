<?php

namespace app\plugins\shopping\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%shopping_like_users}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $like_id
 * @property string $created_at
 * @property int $is_delete
 */
class ShoppingLikeUsers extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shopping_like_users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'like_id', 'created_at'], 'required'],
            [['user_id', 'like_id', 'is_delete'], 'integer'],
            [['created_at'], 'safe'],
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
            'like_id' => 'Like ID',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
