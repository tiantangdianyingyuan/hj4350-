<?php

namespace app\plugins\region\models;

/**
 * This is the model class for table "{{%region_area}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 区域名称
 * @property string $province_rate 省代理分红比例
 * @property string $city_rate 市代理分红比例
 * @property string $district_rate 区/县分红比例
 * @property string $province_condition 省代理条件
 * @property string $city_condition 市代理条件
 * @property string $district_condition 区/县代理条件
 * @property int $become_type 1:下线总人数 2:分销订单总数 3:分销订单总金额 4:累计佣金总额 5:已提现佣金总额 6:消费金额
 * @property int $is_delete
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 * @property RegionAreaDetail areaDetail
 */
class RegionArea extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%region_area}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'is_delete'], 'integer'],
            [['deleted_at', 'created_at', 'updated_at'], 'required'],
            [
                [
                    'province_rate',
                    'city_rate',
                    'district_rate',
                    'province_condition',
                    'city_condition',
                    'district_condition',
                    'become_type'
                ],
                'number'
            ],
            [['deleted_at', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
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
            'name' => '区域名称',
            'province_rate' => '省代理分红比例',
            'city_rate' => '市代理分红比例',
            'district_rate' => '区/县分红比例',
            'province_condition' => '省代理条件',
            'city_condition' => '市代理条件',
            'district_condition' => '区/县代理条件',
            'become_type' => '条件类型',
            'is_delete' => 'Is Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAreaDetail()
    {
        return $this->hasMany(RegionAreaDetail::className(), ['area_id' => 'id']);
    }
}
