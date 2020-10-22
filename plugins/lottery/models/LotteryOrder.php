<?php

namespace app\plugins\lottery\models;

use app\models\Order;
use app\models\ModelActiveRecord;

/**
 * This is the model class for table "{{%lottery_order_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $lottery_log_id
 * @property int $order_id
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $deleted_at
 */
class LotteryOrder extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lottery_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'lottery_log_id', 'order_id', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'lottery_log_id', 'order_id', 'is_delete'], 'integer'],
            [['created_at', 'deleted_at'], 'safe'],
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
            'lottery_log_id' => 'Lottery Log ID',
            'order_id' => 'Order ID',
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
