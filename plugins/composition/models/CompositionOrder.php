<?php

namespace app\plugins\composition\models;

use Yii;

/**
 * This is the model class for table "{{%composition_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $composition_id 优惠金额
 * @property string $price
 * @property int $is_delete
 */
class CompositionOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%composition_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'price', 'is_delete'], 'required'],
            [['mall_id', 'order_id', 'composition_id', 'is_delete'], 'integer'],
            [['price'], 'number'],
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
            'composition_id' => '优惠金额',
            'price' => 'Price',
            'is_delete' => 'Is Delete',
        ];
    }
}
