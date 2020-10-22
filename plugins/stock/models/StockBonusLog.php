<?php

namespace app\plugins\stock\models;

use Yii;

/**
 * This is the model class for table "{{%stock_bonus_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $bonus_type 1按周，2按月
 * @property string $bonus_price 分红金额
 * @property string $bonus_rate 当时的分红比例
 * @property int $order_num 分红订单数
 * @property int $stock_num 当时股东人数
 * @property string $start_time 分红时间段-开始时间
 * @property string $end_time 分红时间段-结束时间
 * @property string $created_at
 * @property string $updated_at
 */
class StockBonusLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_bonus_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'bonus_type', 'order_num', 'stock_num'], 'integer'],
            [['bonus_price', 'bonus_rate'], 'number'],
            [['start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
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
            'bonus_type' => '1按周，2按月',
            'bonus_price' => '分红金额',
            'bonus_rate' => '当时的分红比例',
            'order_num' => '分红订单数',
            'stock_num' => '当时股东人数',
            'start_time' => '分红时间段-开始时间',
            'end_time' => '分红时间段-结束时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
