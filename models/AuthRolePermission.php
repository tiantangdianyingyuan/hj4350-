<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%auth_role_permission}}".
 *
 * @property int $id
 * @property int $role_id
 * @property string $permissions
 * @property int $is_delete
 */
class AuthRolePermission extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_role_permission}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'permissions'], 'required'],
            [['role_id', 'is_delete'], 'integer'],
            [['permissions'], 'string'],
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
            'permissions' => 'Permissions',
            'is_delete' => 'Is Delete',
        ];
    }
}
