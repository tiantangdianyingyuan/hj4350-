<?php

namespace app\plugins\region\models;

/**
 * This is the model class for table "{{%region_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property string $total_pay_price 订单实付金额
 * @property int $is_bonus 1已分红，0未分红
 * @property string $bonus_rate 分红比例
 * @property string $bonus_time 分红时间
 * @property int $bonus_id 区域完成分红记录ID
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区
 * @property int $province_id
 * @property int $city_id
 * @property int $district_id
 */
class RegionOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['mall_id', 'order_id', 'is_bonus', 'bonus_id', 'is_delete', 'province_id', 'city_id', 'district_id'],
                'integer'
            ],
            [['total_pay_price', 'bonus_rate'], 'number'],
            [['bonus_time', 'deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['province', 'city', 'district'], 'required'],
            [['province', 'city', 'district'], 'string', 'max' => 20],
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
            'total_pay_price' => '订单实付金额',
            'is_bonus' => '1已分红，0未分红',
            'bonus_rate' => '分红比例',
            'bonus_time' => '分红时间',
            'bonus_id' => '区域完成分红记录ID',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'province' => '省',
            'city' => '市',
            'district' => '区',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
        ];
    }
}
