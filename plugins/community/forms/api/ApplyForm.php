<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/3
 * Time: 17:30
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;


use app\models\DistrictArr;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;
use app\plugins\community\jobs\ApplyJob;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityMiddleman;

class ApplyForm extends Model
{
    public $name;
    public $mobile;
    public $detail;

    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $pay_price;
    public $token;

    public function rules()
    {
        return [
            [['name', 'province_id', 'city_id', 'district_id', 'mobile', 'detail', 'latitude', 'longitude', 'location'], 'required'],
            [['detail'], 'string'],
            [['province_id', 'city_id', 'district_id', 'pay_price'], 'integer'],
            [['name', 'mobile', 'latitude', 'longitude', 'location'], 'string', 'max' => 255],
            ['pay_price', 'default', 'value' => 0],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
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

    public function apply()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $token = \Yii::$app->security->generateRandomString();
        $queueId = \Yii::$app->queue->delay(0)->push(new ApplyJob([
            'form' => $this,
            'token' => $token,
            'mall' => \Yii::$app->mall,
            'user' => \Yii::$app->user->identity,
            'appVersion' => \Yii::$app->appVersion,
        ]));
        return $this->success([
            'token' => $token,
            'queue_id' => $queueId
        ]);
    }

    public function save()
    {
        $form = new AddressForm();
        $form->attributes = $this->attributes;
        $address = $form->saveAddress();
        $setting = CommonSetting::getCommon()->getSetting();
        $common = CommonMiddleman::getCommon();
        $model = $common->getConfig(\Yii::$app->user->id);
        if (!$model) {
            $model = new CommunityMiddleman();
            $model->user_id = \Yii::$app->user->id;
            $model->mall_id = \Yii::$app->mall->id;
        } else {
            if (!in_array($model->status, [2, 3])) {
                throw new \Exception('不需要重复申请');
            }
        }
        $model->token = $this->token;
        $model->mobile = $this->mobile;
        $model->name = $this->name;
        $model->is_delete = 0;
        $model->delete_first_show = 1;
        $model->apply_at = mysql_timestamp();
        $model->pay_time = '0000-00-00 00:00:00';
        $model->pay_type = 0;
        if ($setting['is_apply_money'] == 1) {
            $model->status = -1;
            $model->pay_price = price_format($setting['apply_money']);
        } else {
            $model->pay_price = 0;
            if ($setting['is_apply'] == 1) {
                $model->status = 0;
            } else {
                $model->status = 1;
                $model->become_at = mysql_timestamp();
                $model->reason = '无需审核';
            }
        }
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        return true;
    }
}
