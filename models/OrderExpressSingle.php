<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_express_single}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id 订单id
 * @property string $express_code 快递公司编码
 * @property string $ebusiness_id 快递鸟id
 * @property string $print_teplate
 * @property string $order 订单信息
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 */
class OrderExpressSingle extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_express_single}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'express_code', 'ebusiness_id', 'print_teplate', 'order', 'is_delete', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'order_id', 'is_delete'], 'integer'],
            [['print_teplate', 'order'], 'string'],
            [['created_at', 'deleted_at'], 'safe'],
            [['express_code', 'ebusiness_id'], 'string', 'max' => 255],
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
            'order_id' => '订单id',
            'express_code' => '快递公司编码',
            'ebusiness_id' => '快递鸟id',
            'print_teplate' => 'Print Teplate',
            'order' => '订单信息',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
