<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%city_preview_order}}".
 *
 * @property int $id
 * @property array $result_data
 * @property array $order_info
 * @property array $all_order_info
 * @property string $order_detail_sign
 * @property string $created_at
 */
class CityPreviewOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city_preview_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['result_data', 'order_info', 'created_at', 'all_order_info'], 'safe'],
            [['order_detail_sign'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'result_data' => 'Result Data',
            'order_info' => 'Order Info',
            'all_order_info' => 'All Order Info',
            'order_detail_sign' => 'Order Detail Sign',
            'created_at' => 'Created At',
        ];
    }
}
