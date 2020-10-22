<?php

namespace app\plugins\scratch\models;

use app\models\Order;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%scratch_order_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $scratch_log_id
 * @property int $order_id
 * @property string $created_at
 * @property int $is_delete
 * @property string $deleted_at
 */
class ScratchOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scratch_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'scratch_log_id', 'order_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'scratch_log_id', 'order_id', 'is_delete'], 'integer'],
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
            'scratch_log_id' => 'Scratch Log ID',
            'order_id' => 'Order ID',
            'created_at' => 'Created At',
            'is_delete' => '删除',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
