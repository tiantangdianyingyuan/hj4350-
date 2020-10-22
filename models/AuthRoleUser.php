<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%auth_role_user}}".
 *
 * @property int $id
 * @property int $role_id
 * @property int $user_id
 * @property int $is_delete
 */
class AuthRoleUser extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_role_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'user_id'], 'required'],
            [['role_id', 'user_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'user_id' => 'User ID',
            'is_delete' => 'Is Delete',
        ];
    }
}
