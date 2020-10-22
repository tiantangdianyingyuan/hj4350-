<?php

namespace app\models;

use yii\helpers\Html;

/**
 * This is the model class for table "{{%address}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name 收货人
 * @property int $province_id
 * @property string $province 省份名称
 * @property int $city_id
 * @property string $city 城市名称
 * @property int $district_id
 * @property string $district 县区名称
 * @property string $mobile 联系电话
 * @property string $detail 详细地址
 * @property int $is_default 是否默认
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $latitude 经度
 * @property string $longitude 纬度
 * @property string $location 位置
 * @property int $type 类型：0快递 1同城
 */
class Address extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'mobile', 'detail', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'province_id', 'city_id', 'district_id', 'is_default', 'is_delete', 'type'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'province', 'city', 'district', 'mobile', 'latitude', 'longitude', 'location'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => '收货人',
            'province_id' => 'Province ID',
            'province' => '省份名称',
            'city_id' => 'City ID',
            'city' => '城市名称',
            'district_id' => 'District ID',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
            'is_default' => '是否默认',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'latitude' => '经度',
            'longitude' => '纬度',
            'location' => '位置',
            'type' => '类型：0快递 1同城'
        ];
    }

    public function beforeSave($insert)
    {
        $this->name = Html::encode($this->name);
        $this->mobile = Html::encode($this->mobile);
        $this->detail = Html::encode($this->detail);
        return parent::beforeSave($insert);
    }
}
