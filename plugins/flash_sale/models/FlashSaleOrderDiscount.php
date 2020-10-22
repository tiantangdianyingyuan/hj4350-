<?php

namespace app\plugins\flash_sale\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%flash_sale_order_discount}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id 订单id
 * @property string $discount 优惠金额
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class FlashSaleOrderDiscount extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%flash_sale_order_discount}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'order_id', 'is_delete'], 'integer'],
            [['discount'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'order_id' => 'Order ID',
            'discount' => 'Discount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
