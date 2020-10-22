<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_detail_vip_card_info}}".
 *
 * @property int $id
 * @property int $vip_card_order_id
 * @property int $order_detail_id
 * @property string $order_detail_total_price
 */
class OrderDetailVipCardInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail_vip_card_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vip_card_order_id', 'order_detail_id', 'order_detail_total_price'], 'required'],
            [['vip_card_order_id', 'order_detail_id'], 'integer'],
            [['order_detail_total_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vip_card_order_id' => 'Vip Card Order ID',
            'order_detail_id' => 'Order Detail ID',
            'order_detail_total_price' => 'Order Detail Total Price',
        ];
    }
}
