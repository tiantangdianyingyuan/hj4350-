<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\delivery;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\forms\common\map\Map;
use app\models\CityDeliverySetting;
use app\models\Model;
use yii\db\Exception;

class DeliveryForm extends Model
{
    public $mall_id;
    public $key;
    public $value;

    public $set_data;

    //默认数据
    private $default = [
        'is_superposition' => 0, //是否叠加 1叠加0不
        'mobile' => [],
        'price_mode' => ["start_price" => "0", "start_distance" => "0", "add_distance" => "0", "add_price" => "0", "fixed" => "0"], //计费方式
        'web_key' => '', //高德key
        'address' => ["address" => "", "longitude" => "", "latitude" => ""], //配送起点地址
        'explain' => '', //配送说明（配送时间什么的）
        'range' => [], //经纬度划定范围
        'price_enable' => 0,
        'contact_way' => '',
        'is_free_delivery' => 0,
        'free_delivery' => 0,
    ];

    public function rules()
    {
        return [
            [['mall_id'], 'integer'],
            [['key'], 'string', 'max' => 60],
            [['value'], 'string'],
            ['set_data', 'trim'],
        ];
    }

    public function getData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $list = $this->getDeliveryData();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    public function getDeliveryData()
    {
        $data = CityDeliverySetting::find()->select('key,value')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        $list = [];
        if ($data && !empty($data)) {
            foreach ($data as $value) {
                $list[$value['key']] = json_decode($value['value'], true);
            }
        }
        foreach ($this->default as $key => $item) {
            if (!isset($list[$key])) {
                $list[$key] = $item;
            }
        }
        $list['mobile'] = CommonDelivery::getInstance()->getManList();

        return $list;
    }

    public function edit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->set_data) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '参数有误',
            ];
        }

        $set_data = json_decode($this->set_data, true);

        $t = \Yii::$app->db->beginTransaction();
        try {
            foreach ($set_data as $key => $value) {
                if ($key == 'mobile') {
                    continue;
                }
                if ($key == 'range' && (!$value || empty($value))) {
                    throw new \Exception('请选择配送范围');
                }
                if ($key == 'contact_way' && (!$value || empty($value))) {
                    throw new \Exception('请输入联系方式');
                }
                if ($key == 'free_delivery') {
                    if (!$value) {
                        $value = 0;
                    }
                    if ($value < 0) {
                        throw new \Exception('满足金额必须不小于0');
                    }
                }
                if ($key == 'price_mode') {
                    foreach ($value as $index => $item) {
                        if (!is_numeric($item)) {
                            throw new \Exception('计费方式必须是数字');
                        }
                        if (in_array($index, ['start_distance', 'add_distance']) && !is_integer($item * 1)) {
                            throw new \Exception('计费方式公里数必须是整数');
                        }
                    }
                }
                if ($key == 'web_key') {
                    $map = new Map();
                    $map->check($value);
                }
                $model = CityDeliverySetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $key, 'is_delete' => 0]);
                if (empty($model)) {
                    $model = new CityDeliverySetting();
                    $model->mall_id = \Yii::$app->mall->id;
                    $model->key = $key;
                }
                $model->value = json_encode($value, JSON_UNESCAPED_UNICODE);
                if (!$model->save()) {
                    throw new Exception($this->getErrorMsg($model));
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    //获取配送人员手机
    public function mobile()
    {
        $model = CityDeliverySetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'mobile', 'is_delete' => 0]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'mobile' => !empty($model) ? $model->value : '',
            ],
        ];
    }
}
