<?php

namespace app\plugins\check_in\models;

use Yii;

/**
 * This is the model class for table "{{%check_in_queue}}".
 *
 * @property int $id
 * @property string $token
 * @property string $data
 */
class CheckInQueue extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%check_in_queue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'data'], 'required'],
            [['data'], 'string'],
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
            'token' => 'Token',
            'data' => 'Data',
        ];
    }
}
