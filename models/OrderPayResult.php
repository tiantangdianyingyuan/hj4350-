<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_pay_result}}".
 *
 * @property int $id
 * @property int $order_id
 * @property string $data json数据
 * @property string $created_at
 * @property string $updated_at
 */
class OrderPayResult extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_pay_result}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'data' => 'json数据',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function encodeData($data)
    {
        return Yii::$app->serializer->encode($data);
    }

    public function decodeData($data)
    {
        return Yii::$app->serializer->decode($data);
    }
}
