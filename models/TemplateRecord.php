<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%template_record}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $status 模板消息是否发送成功0--失败|1--成功
 * @property string $data 模板消息内容
 * @property string $error 错误信息
 * @property string $created_at
 * @property string $token 模板消息发送标示
 */
class TemplateRecord extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%template_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'data', 'error'], 'required'],
            [['mall_id', 'user_id', 'status'], 'integer'],
            [['data', 'error'], 'string'],
            [['created_at'], 'safe'],
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
            'status' => '模板消息是否发送成功0--失败|1--成功',
            'data' => '模板消息内容',
            'error' => '错误信息',
            'created_at' => 'Created At',
            'token' => '模板消息发送标示',
        ];
    }
}
