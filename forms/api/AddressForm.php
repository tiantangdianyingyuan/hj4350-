<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\models\Address;
use app\models\DistrictArr;
use app\models\Model;

class AddressForm extends Model
{
    public $id;
    public $name;
    public $limit;

    public $mobile;
    public $detail;
    public $is_default;

    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $type;

    public $hasCity;

    public function rules()
    {
        return [
            [['name', 'province_id', 'city_id', 'district_id', 'mobile', 'detail'], 'required'],
            [['detail', 'hasCity'], 'string'],
            [['id', 'province_id', 'city_id', 'district_id', 'is_default', 'limit', 'type'], 'integer'],
            [['is_default', 'type'], 'default', 'value' => 0],
            [['name', 'mobile', 'latitude', 'longitude', 'location'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '收货人',
            'province_id' => 'Province ID',
            'province' => '省份名称',
            'city_id' => 'City ID',
            'city' => '城市名称',
            'district_id' => 'District ID',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
            'latitude' => '定位地址',
            'longitude' => '定位地址',
            'location' => '定位地址',
        ];
    }

    public function autoAddressInfo()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => file_get_contents('statics' . DIRECTORY_SEPARATOR . 'text' . DIRECTORY_SEPARATOR . 'auto_address.json')
        ];
    }

    //GET
    public function getList()
    {
        $query = Address::find()->where([
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->identity->id,
        ]);
        !is_null($this->type) && $query->andWhere(['type' => $this->type]);

        //兼容不限制
        $limit = 9999;
        $list = $query->page($pagination, $limit)
            ->orderBy('is_default DESC,created_at DESC')
            ->asArray()
            ->all();

        $inPointList = [];
        $notInPointList = [];
        foreach ($list as $i => $item) {
            if ($this->type === 1 || $this->hasCity === 'true') {
                $list[$i]['address'] = $item['location'] . ' ' . $item['detail'];
                if (!$item['longitude'] || !$item['latitude']) {
                    $notInPointList[] = $list[$i];
                } else {
                    try {
                        $config = CommonDelivery::getInstance()->getConfig();
                        $range = $config['range'];
                        $point = [
                            'lng' => $item['longitude'],
                            'lat' => $item['latitude']
                        ];
                        if (is_point_in_polygon($point, $range)) {
                            $inPointList[] = $list[$i];
                        } else {
                            $notInPointList[] = $list[$i];
                        }
                    } catch (\Exception $exception) {
                        $notInPointList[] = $list[$i];
                    }
                }
            } else {
                $list[$i]['address'] = $item['province'] . $item['city'] . $item['district'] . $item['detail'];
                $inPointList[] = $list[$i];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $inPointList,
                'notInPointList' => $notInPointList,
            ]
        ];
    }

    public function detail()
    {
        $user_id = \Yii::$app->user->identity->id;
        $list = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //DEFAULT
    public function default()
    {
        $user_id = \Yii::$app->user->identity->id;

        Address::updateAll(['is_default' => 0], [
            'is_delete' => 0,
            'user_id' => $user_id,
            'type' => $this->type,
        ]);
        $model = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
            'type' => $this->type,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_default = $this->is_default;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功'
        ];
    }

    //DELETE
    public function destroy()
    {
        $user_id = \Yii::$app->user->identity->id;
        $model = Address::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $user_id = \Yii::$app->user->identity->id;
            $address = Address::findOne([
                'id' => $this->id,
                'is_delete' => 0,
                'user_id' => $user_id
            ]);

            if (!$address) {
                $address = new Address();
                $address->is_delete = 0;
                $address->type = $this->type;
                $address->user_id = $user_id;
            }
            $address->attributes = $this->attributes;

            if ($address->type) {
                $this->saveCity($address);
            } else {
                $this->saveExpress($address);
            }
            $list = Address::find()->where(['user_id' => $user_id, 'is_delete' => 0])->all();
            if (!$list || count($list) === 0) {
                $address->is_default = 1;
            }
            if (!$address->save()) {
                throw new \Exception($this->getErrorMsg($address));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
    //保留
    private function checkOld()
    {
        $version = $_SERVER['HTTP_X_APP_VERSION'];
        $limit = substr_count($version, '.');
        if ($limit < 2) {
            $version .= str_repeat('.0', 2 - $limit);
        }
        $compare = '4.2.76';
        if (preg_match('/(\d+).(\d+).(\d+)/', $version, $matches)) {
            array_shift($matches);
            $arr = explode('.', $compare);
            for ($i = 0; $i < 3; $i++) {
                $compare = $matches[$i] <=> $arr[$i];
                if ($compare != 0) {
                    break;
                }
            }
            if ($compare < 0) {
                $isRequiredPosition = \Yii::$app->mall->getMallSettingOne('is_required_position');
                if ($isRequiredPosition && \Yii::$app->appPlatform != APP_PLATFORM_TTAPP) {
                    if ($this->longitude == '' || $this->latitude == '' || $this->location == '') {
                        throw new \Exception('定位地址不能为空');
                    }
                }
            }
        }
    }

    /**
     * @param $address
     * @throws \Exception
     */
    private function saveExpress(&$address)
    {
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
        $address->province = $province->name;
        $address->city = $city->name;
        $address->district = $district->name;
        $address->province_id = $this->province_id;
        $address->city_id = $this->city_id;
        $address->district_id = $this->district_id;
    }

    /**
     * @param $address
     * @throws \Exception
     */
    private function saveCity(&$address)
    {
        $isRequiredPosition = \Yii::$app->mall->getMallSettingOne('is_required_position');
        if ($isRequiredPosition && \Yii::$app->appPlatform != APP_PLATFORM_TTAPP) {
            if ($this->longitude == '' || $this->latitude == '' || $this->location == '') {
                throw new \Exception('定位地址不能为空');
            }
        }
        $address->province = '';
        $address->city = '';
        $address->district = '';
        $address->province_id = 0;
        $address->city_id = 0;
        $address->district_id = 0;
    }
}
