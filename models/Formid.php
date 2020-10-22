<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%formid}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $form_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $remains
 * @property string $expired_at
 */
class Formid extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%formid}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'form_id', 'remains', 'expired_at'], 'required'],
            [['user_id', 'remains'], 'integer'],
            [['created_at', 'updated_at', 'expired_at'], 'safe'],
            [['form_id'], 'string', 'max' => 1000],
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
            'form_id' => 'Form ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'remains' => 'Remains',
            'expired_at' => 'Expired At',
        ];
    }
}
