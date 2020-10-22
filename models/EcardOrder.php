<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ecard_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $ecard_id
 * @property string $value
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $is_delete
 * @property string $token 加密字符串
 * @property int $ecard_options_id
 * @property int $user_id 用户id
 * @property string $order_token 订单token
 */
class EcardOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ecard_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'ecard_id', 'value', 'order_id', 'order_detail_id', 'is_delete', 'ecard_options_id'], 'required'],
            [['mall_id', 'ecard_id', 'order_id', 'order_detail_id', 'is_delete', 'ecard_options_id', 'user_id'], 'integer'],
            [['value'], 'string'],
            [['token', 'order_token'], 'string', 'max' => 255],
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
            'ecard_id' => 'Ecard ID',
            'value' => 'Value',
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'is_delete' => 'Is Delete',
            'token' => '加密字符串',
            'ecard_options_id' => 'Ecard Options ID',
            'user_id' => '用户id',
            'order_token' => '订单token',
        ];
    }
}
