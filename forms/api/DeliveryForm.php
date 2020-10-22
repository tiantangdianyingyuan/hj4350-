<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\models\CityDeliverySetting;
use app\models\CityService;
use app\models\Model;

class DeliveryForm extends Model
{
    public $distance;
    public $num;

    public function rules()
    {
        return [
            [['distance', 'num'], 'integer'],
            [['distance', 'num'], 'required'],
        ];
    }

    public function getPrice()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $price = CommonDelivery::getInstance()->getPrice($this->distance, $this->num);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'price' => $price
            ]
        ];
    }

    public function getConfig()
    {
        try {
            $config = CommonDelivery::getInstance()->getConfig();
            if (empty($config)) {
                throw new \Exception('后台未配置同城配送');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'config' => $config
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function getDeliveryman()
    {
        try {
            $list = CommonDelivery::getInstance()->getManList();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list,
                    'city_service_list' => $this->searchCityServiceList(),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function searchCityServiceList()
    {
        $query = CityService::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'platform' => 'wxapp',// 需要根据订单所属平台
            'is_delete' => 0,
        ]);

        $list = $query->orderBy(['created_at' => SORT_DESC])->all();

        return $list;
    }
}
