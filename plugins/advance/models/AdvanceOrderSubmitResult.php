<?php

namespace app\plugins\advance\models;

use Yii;

/**
 * This is the model class for table "{{%advance_order_submit_result}}".
 *
 * @property int $id
 * @property string $token
 * @property string $data
 */
class AdvanceOrderSubmitResult extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advance_order_submit_result}}';
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
