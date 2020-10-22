<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%auth_role}}".
 *
 * @property int $id
 * @property int $creator_id 创建者ID
 * @property int $mall_id
 * @property string $name
 * @property string $remark 角色描述、备注
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class AuthRole extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['creator_id', 'mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['creator_id', 'mall_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'creator_id' => 'Creator ID',
            'mall_id' => 'Mall ID',
            'name' => 'Name',
            'remark' => 'Remark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
    }

    public function getPermissions() {
        return $this->hasOne(AuthRolePermission::className(), ['role_id' => 'id']);
    }
}
