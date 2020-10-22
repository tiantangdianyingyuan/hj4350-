<?php

namespace app\plugins\exchange\models;

use app\models\ModelActiveRecord;
use app\models\Order;
use app\models\User;

/**
 * This is the model class for table "{{%exchange_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $order_id
 * @property int $exchange_id
 * @property int $code_id
 * @property int $goods_id
 * @property int $is_delete
 * @property string $created_at
 */
class ExchangeOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%exchange_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'order_id', 'exchange_id', 'code_id', 'goods_id'], 'required'],
            [['mall_id', 'user_id', 'order_id', 'exchange_id', 'code_id', 'is_delete', 'goods_id'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'exchange_id' => 'Exchange ID',
            'code_id' => 'Code ID',
            'is_delete' => 'Is Delete',
            'goods_id' => 'Goods Id',
            'created_at' => 'Created At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getCode()
    {
        return $this->hasOne(ExchangeCode::className(), ['id' => 'code_id']);
    }

    public function getLibrary()
    {
        return $this->hasOne(ExchangeLibrary::className(), ['id' => 'exchange_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(\app\models\Goods::className(), ['id' => 'goods_id']);
    }
}
