<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%store}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 店铺名称
 * @property string $mobile 联系电话
 * @property string $address 地址
 * @property string $longitude 经度
 * @property string $latitude  纬度
 * @property int $score 店铺评分
 * @property int $province_id 省ID
 * @property int $city_id 市ID
 * @property int $district_id 区ID
 * @property string $cover_url 店铺封面图
 * @property string $pic_url 店铺轮播图
 * @property string $business_hours 营业时间
 * @property string $description 门店描述
 * @property int $is_default 默认总店0.否|1.是
 * @property int $scope 门店经营范围
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $is_all_day
 */
class Store extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%store}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'description', 'created_at', 'updated_at', 'deleted_at', 'pic_url'], 'required'],
            [['mall_id', 'score', 'is_default', 'is_delete', 'mch_id', 'province_id', 'city_id',
                'district_id', 'is_all_day'], 'integer'],
            [['description', 'pic_url', 'scope'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 65],
            [['mobile', 'address', 'longitude', 'latitude', 'cover_url'], 'string', 'max' => 255],
            [['business_hours'], 'string', 'max' => 125],
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
            'name' => '门店名称',
            'mobile' => '门店电话',
            'address' => '地址',
            'longitude' => '经度',
            'latitude' => '纬度',
            'score' => '店铺评分',
            'cover_url' => '店铺图片',
            'pic_url' => '店铺轮播图',
            'business_hours' => '营业时间',
            'description' => '店铺描述',
            'scope' => '门店经营范围',
            'is_default' => '是否默认门店',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_all_day' => '是否全天营业',
        ];
    }
}
