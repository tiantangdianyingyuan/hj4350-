<?php

namespace app\plugins\step\models;

use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%step_order}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $mall_id
 * @property int $num 商品数量
 * @property string $total_pay_price 商品价格
 * @property int $user_id 用户ID
 * @property string $currency
 * @property string $created_at
 * @property string $deleted_at
 * @property string $token
 * @property int $is_delete 删除
 */
class StepOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%step_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'mall_id', 'num', 'user_id', 'is_delete'], 'integer'],
            [['mall_id', 'num', 'total_pay_price', 'user_id', 'currency', 'created_at', 'deleted_at', 'token'], 'required'],
            [['total_pay_price', 'currency'], 'number'],
            [['created_at', 'deleted_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
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
            'mall_id' => 'Mall ID',
            'num' => 'Num',
            'total_pay_price' => 'Total Pay Price',
            'user_id' => 'User ID',
            'currency' => 'Currency',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'token' => 'Token',
            'is_delete' => 'Is Delete',
        ];
    }
}
