<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\store;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Store;
use app\validators\PhoneNumberValidator;

class StoreEditForm extends Model
{
    public $name;
    public $mobile;
    public $address;
    public $cover_url;
    public $pic_url;
    public $score;
    public $business_hours;
    public $latitude_longitude;
    public $longitude;
    public $description;
    public $is_default;
    public $id;
    public $is_all_day;

    public $start_time;
    public $end_time;

    public $isNewRecord;
    public $store;

    public function rules()
    {
        return [
            [['name', 'mobile', 'address', 'cover_url', 'score',
                'latitude_longitude', 'description', 'start_time', 'end_time'], 'required'],
            [['name', 'mobile', 'address', 'cover_url', 'score', 'business_hours',
                'latitude_longitude', 'description', 'longitude', 'start_time', 'end_time'], 'string'],
            [['id', 'is_default', 'is_all_day'], 'integer'],
            [['pic_url'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '门店名称',
            'mobile' => '联系手机号',
            'address' => '门店地址',
            'cover_url' => '门店封面图',
            'score' => '门店评分',
            'latitude_longitude' => '门店经纬度',
            'description' => '门店描述',
            'pic_url' => '门店轮播图',
            'start_time' => '开始营业时间',
            'end_time' => '结束营业时间',
            'is_all_day' => '是否全天营业',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $store = Store::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$store) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $store = new Store();
            }
            $this->isNewRecord = $store->isNewRecord;
            $this->store = $store;
            $latitude_longitude = explode(",", $this->latitude_longitude);

            if (count($latitude_longitude) < 2) {
                throw new \Exception('经纬度填写不合法');
            }

            // TODO 未完善
            $store->name = $this->name;
            $store->mobile = $this->mobile;
            $store->address = $this->address;
            $store->longitude = $latitude_longitude[1];
            $store->latitude = $latitude_longitude[0];
            $store->score = $this->score;
            $store->pic_url = json_encode($this->pic_url);
            $store->cover_url = $this->cover_url;
            $store->business_hours = $this->start_time . '-' . $this->end_time;
            if ($this->is_all_day) {
                $store->business_hours = '00:00-24:00';
            }
            $store->is_all_day = $this->is_all_day;
            $store->mall_id = \Yii::$app->mall->id;
            $store->description = $this->description;
            $store->scope = '';
            $res = $store->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($store));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
