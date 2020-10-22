<?php

namespace app\plugins\vip_card\models;

use Yii;

/**
 * This is the model class for table "{{%vip_card_discount}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $main_id
 * @property string $main_name
 * @property int $detail_id
 * @property string $detail_name
 * @property string $discount_num
 * @property string $discount
 * @property string $created_at
 */
class VipCardDiscount extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vip_card_discount}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_detail_id'], 'required'],
            [['order_id', 'order_detail_id', 'main_id', 'detail_id'], 'integer'],
            [['discount', 'discount_num'], 'number'],
            [['detail_name', 'main_name',], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_detail_id' => 'Order Detail ID',
            'discount' => '折扣优惠',
            'discount_num' => '折扣',
            'created_at' => 'Created At',
        ];
    }
}
