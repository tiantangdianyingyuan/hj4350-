<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_identity}}".
 *
 * @property int $id 用户身份表
 * @property int $user_id
 * @property int $is_super_admin 是否为超级管理员
 * @property int $is_admin 是否为管理员
 * @property int $is_operator 是否为操作员
 * @property int $member_level 会员等级:0.普通成员
 * @property int $is_distributor 是否为分销商
 * @property int $is_delete 是否删除
 */
class UserIdentity extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_identity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_super_admin', 'is_admin', 'is_operator', 'member_level',
                'is_distributor', 'user_id', 'is_delete'], 'integer'],
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
            'is_super_admin' => 'Is Super Admin',
            'is_admin' => 'Is Admin',
            'is_operator' => 'Is Operator',
            'is_delete' => 'Is Delete',
            'member_level' => 'Member Level',
            'is_distributor' => 'Is Distributor',
        ];
    }
}
