<?php

namespace app\models;

use app\models\Order;
use app\models\OrderExpressSingle;

/**
 * This is the model class for table "{{%order_detail_express}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $send_type;
 * @property string $city_name;
 * @property string $city_info;
 * @property string $city_mobile;
 * @property string $shop_order_id;
 * @property string $status;
 * @property string $express
 * @property string $express_no
 * @property string $merchant_remark 商家留言
 * @property string $express_content 物流内容
 * @property string $customer_name 京东物流编码
 * @property int $is_delete
 * @property int $order_id
 * @property int $express_single_id
 * @property int $city_service_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property OrderDetailExpressRelation $expressRelation
 */
class OrderDetailExpress extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail_express}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'created_at', 'updated_at', 'deleted_at', 'send_type'], 'required'],
            [['mall_id', 'mch_id', 'is_delete', 'send_type', 'order_id', 'express_single_id', 'status', 'city_service_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['express'], 'string', 'max' => 65],
            [['city_info'], 'string'],
            [['express_no', 'merchant_remark', 'express_content', 'customer_name', 'city_name', 'shop_order_id', 'city_mobile'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'express' => 'Express',
            'send_type' => '1.快递|2.其它方式',
            'express_no' => 'Express No',
            'merchant_remark' => '商家留言',
            'express_content' => '物流内容',
            'customer_name' => '京东物流编码',
            'is_delete' => 'Is Delete',
            'order_id' => 'Order Id',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'express_single_id' => '电子面单ID',
        ];
    }

    public function getExpressRelation()
    {
        return $this->hasMany(OrderDetailExpressRelation::className(), ['order_detail_express_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getExpressSingle()
    {
        return $this->hasOne(OrderExpressSingle::className(), ['id' => 'express_single_id'])->andWhere(['is_delete' => 0]);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id'])->andWhere(['is_delete' => 0]);
    }

    public function getExpressCityText($express)
    {
        $text = '';
        if ($express['send_type'] == 1 && $express['status'] && !in_array($express['status'], [101, 102, 202, 301, 302])) {
            $cityInfo = json_decode($express['city_info'], true);
            switch ($express['express_type']) {
                case '微信':
                    # code...
                    break;
                case '顺丰同城急送':
                    if (isset($cityInfo[$express['status']]) && isset($cityInfo[$express['status']]['cancel_reason'])) {
                        $text = $cityInfo[$express['status']]['cancel_reason'];
                    }
                    break;
                case '达达':
                    if (isset($cityInfo[$express['status']]) && isset($cityInfo[$express['status']]['cancel_reason'])) {
                        $text = $cityInfo[$express['status']]['cancel_reason'];
                    }
                    break;
                case '闪送':
                    if (isset($cityInfo[$express['status']]) && isset($cityInfo[$express['status']]['abortReason'])) {
                        $text = $cityInfo[$express['status']]['abortReason'];
                    }
                    break;
                default:
                    # code...
                    break;
            }
            $text = $text ?: '配送订单异常 已取消';
        }

        return $text;
    }
}
