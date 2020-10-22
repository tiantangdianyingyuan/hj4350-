<?php

namespace app\plugins\integral_mall\models;

use Yii;

/**
 * This is the model class for table "{{%integral_mall_orders}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property string $token
 * @property int $integral_num 商品所需积分
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 * @property Order $order
 */
class IntegralMallOrders extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integral_mall_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'integral_num', 'created_at', 'deleted_at', 'order_id'], 'required'],
            [['mall_id', 'integral_num', 'is_delete', 'order_id'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'order_id' => 'Order ID',
            'token' => 'Token',
            'integral_num' => '商品所需积分',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
