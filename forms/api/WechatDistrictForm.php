<?php

namespace app\forms\api;


use app\models\DistrictArr;
use app\models\Model;


class WechatDistrictForm extends Model
{
    public $national_code;

    public $province_name;
    public $city_name;
    public $county_name;

    public function rules()
    {
        return [
            [['national_code', 'province_name', 'city_name', 'county_name',], 'safe',],
        ];
    }

    public function getList()
    {
        if (!$this->validate())
            return $this->errorResponse;
        return DistrictArr::getWechatDistrict($this->province_name, $this->city_name, $this->county_name);
    }

}
