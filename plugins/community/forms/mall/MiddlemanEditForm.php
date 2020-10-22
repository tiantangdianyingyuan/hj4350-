<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/12
 * Time: 3:35 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\mall;


use app\models\DistrictArr;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityMiddleman;

class MiddlemanEditForm extends Model
{
    public $user_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $detail;
    public $mobile;

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['detail'], 'string'],
            [['province_id', 'city_id', 'district_id', 'user_id'], 'integer'],
            [['latitude', 'longitude', 'location', 'mobile'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
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
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /** @var CommunityMiddleman $middleman */
            $middleman = CommunityMiddleman::find()->with()
                ->where(['mall_id' => \Yii::$app->mall->id, 'user_id' => $this->user_id, 'is_delete' => 0])
                ->one();
            if (!$middleman) {
                throw new \Exception('团长不存在或已删除');
            }
            if ($middleman->status != 1) {
                throw new \Exception('团长未审核通过，无法修改');
            }
            $middleman->mobile = $this->mobile;
            if (!$middleman->save()) {
                throw new \Exception($this->getErrorMsg($middleman));
            }
            $province = DistrictArr::getDistrict($this->province_id);
            $city = DistrictArr::getDistrict($this->city_id);
            $district = DistrictArr::getDistrict($this->district_id);
            $middleman->address->attributes = $this->attributes;
            $middleman->address->province = $province->name;
            $middleman->address->city = $city->name;
            $middleman->address->district = $district->name;
            if (!$middleman->address->save()) {
                throw new \Exception($this->getErrorMsg($middleman->address));
            }
            return $this->success(['msg' => '保存成功']);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }
}
