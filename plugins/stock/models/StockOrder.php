<?php

namespace app\plugins\stock\models;

use Yii;

/**
 * This is the model class for table "{{%stock_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property string $total_pay_price 订单实付金额
 * @property int $is_bonus 1已分红，0未分红
 * @property string $bonus_time 分红时间
 * @property int $bonus_id 股东完成分红记录ID
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 */
class StockOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'is_bonus', 'bonus_id', 'is_delete'], 'integer'],
            [['total_pay_price'], 'number'],
            [['bonus_time', 'deleted_at', 'created_at', 'updated_at'], 'safe'],
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
            'total_pay_price' => '订单实付金额',
            'is_bonus' => '1已分红，0未分红',
            'bonus_time' => '分红时间',
            'bonus_id' => '股东完成分红记录ID',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
