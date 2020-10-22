<?php

namespace app\plugins\scan_code_pay\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%scan_code_pay_orders}}".
 *
 * @property int $id
 * @property int $order_id
 * @property number $activity_preferential_price 活动优惠价格
 */
class ScanCodePayOrders extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%scan_code_pay_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
            [['activity_preferential_price'], 'number']
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
            'activity_preferential_price' => '活动优惠价格',
        ];
    }
}
