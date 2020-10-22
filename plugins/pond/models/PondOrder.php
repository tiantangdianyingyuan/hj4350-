<?php

namespace app\plugins\pond\models;

use app\models\Order;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%pond_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $pond_log_id
 * @property int $order_id
 * @property string $created_at
 * @property int $is_delete
 * @property string $deleted_at
 */
class PondOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'pond_log_id', 'order_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'pond_log_id', 'order_id', 'is_delete'], 'integer'],
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
            'pond_log_id' => 'Pond Log ID',
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
