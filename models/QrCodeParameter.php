<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%qr_code_parameter}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $token
 * @property string $data
 * @property string $created_at
 * @property string $path 小程序路径
 * @property int $use_number 使用次数
 */
class QrCodeParameter extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%qr_code_parameter}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'data', 'created_at', 'path'], 'required'],
            [['mall_id', 'user_id', 'use_number'], 'integer'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['token'], 'string', 'max' => 30],
            [['path'], 'string', 'max' => 255],
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
            'data' => 'Data',
            'created_at' => 'Created At',
            'path' => '小程序路径',
            'use_number' => '使用次数'
        ];
    }
}
