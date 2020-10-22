<?php

namespace app\plugins\mch\models;

use Yii;

/**
 * This is the model class for table "{{%user_auth_login}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $token
 * @property int $is_pass 是否确认登录
 * @property string $created_at
 * @property string $updated_at
 */
class UserAuthLogin extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_auth_login}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'is_pass'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
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
            'token' => 'Token',
            'is_pass' => '是否确认登录',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
