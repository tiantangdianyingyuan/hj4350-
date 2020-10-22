<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_submit_result}}".
 *
 * @property int $id
 * @property string $token
 * @property resource $data
 */
class OrderSubmitResult extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_submit_result}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
            [['data'], 'string'],
            [['token'], 'string', 'max' => 32],
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
