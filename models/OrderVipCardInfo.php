<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_vip_card_info}}".
 *
 * @property int $id
 * @property int $order_id 订单ID
 * @property int $vip_card_detail_id 超级会员卡子卡ID
 * @property string $order_total_price 超级会员卡优惠后订单的金额
 */
class OrderVipCardInfo extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_vip_card_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'vip_card_detail_id', 'order_total_price'], 'required'],
            [['order_id', 'vip_card_detail_id'], 'integer'],
            [['order_total_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单ID',
            'vip_card_detail_id' => '超级会员卡子卡ID',
            'order_total_price' => '超级会员卡优惠后订单的金额',
        ];
    }
}
