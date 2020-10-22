<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/11
 * Time: 16:18
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\models\DistrictArr;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityAddress;

class AddressForm extends Model
{
    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $detail;
    public $name;
    public $mobile;

    public function rules()
    {
        return [
            [['detail'], 'required'],
            [['detail'], 'string'],
            [['province_id', 'city_id', 'district_id'], 'integer'],
            [['latitude', 'longitude', 'location', 'name', 'mobile'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '团长名称',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
            'mobile' => '联系电话',
            'detail' => '提货地址',
            'latitude' => '定位地址',
            'longitude' => '定位地址',
            'location' => '小区名称',
        ];
    }

    public function save()
    {
        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }
            /** @var CommunityAddress $address */
            $address = CommunityAddress::find()->where([
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0
            ])->one();
            if (!$address) {
                throw new \Exception('未申请成为团长');
            }
            $address->detail = $this->detail;
            if (!$address->save()) {
                throw new \Exception($this->getErrorMsg($address));
            }
            return $this->success(['msg' => '保存成功']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    public function saveAddress()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $province = DistrictArr::getDistrict($this->province_id);
        if (!$province) {
            throw new \Exception('省份数据错误，请重新选择');
        }

        $city = DistrictArr::getDistrict($this->city_id);
        if (!$city) {
            throw new \Exception('城市数据错误，请重新选择');
        }

        $district = DistrictArr::getDistrict($this->district_id);
        if (!$district) {
            throw new \Exception('地区数据错误，请重新选择');
        }
        $address = CommunityAddress::find()->where([
            'user_id' => \Yii::$app->user->id,
        ])->one();
        if (!$address) {
            $address = new CommunityAddress();
            $address->user_id = \Yii::$app->user->id;
        }
        $address->attributes = $this->attributes;
        $address->is_delete = 0;
        $address->is_default = 1;
        $address->province = $province->name;
        $address->city = $city->name;
        $address->district = $district->name;
        if (!$address->save()) {
            throw new \Exception($this->getErrorMsg($address));
        }
        return $address;
    }
}
