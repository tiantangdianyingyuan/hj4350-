<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_detail_express_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $order_detail_express_id
 * @property int $is_delete
 * @property $orderExpress
 * @property OrderDetail $orderDetail
 */
class OrderDetailExpressRelation extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail_express_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_detail_id', 'mall_id', 'mch_id', 'order_detail_express_id'], 'required'],
            [['order_id', 'order_detail_id', 'is_delete', 'mall_id', 'mch_id', 'order_detail_express_id'], 'integer'],
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
            'mch_id' => 'Mch ID',
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getOrderExpress()
    {
        return $this->hasOne(OrderDetailExpress::className(), ['id' => 'order_detail_express_id']);
    }

    public function getOrderDetail() {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id'])->andWhere(['is_delete' => 0]);
    }
}
