<?php

namespace app\plugins\gift\models;

use Yii;

/**
 * This is the model class for table "{{%gift_open_result}}".
 *
 * @property int $id
 * @property string $token
 * @property string $data
 */
class GiftOpenResult extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%gift_open_result}}';
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
