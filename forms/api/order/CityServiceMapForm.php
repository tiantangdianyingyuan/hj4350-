<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\response\ApiCode;
use app\forms\mall\city_service\CityServiceForm;
use app\forms\mall\delivery\DeliveryForm;
use app\models\CityService;
use app\models\Model;
use app\models\OrderDetailExpress;

class CityServiceMapForm extends Model
{
    public $express_id;

    public function rules()
    {
        return [
            [['express_id'], 'required'],
            [['express_id'], 'integer'],
        ];
    }

    public function cityMap()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $express = OrderDetailExpress::find()->andWhere([
                'id' => $this->express_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
            ])->with('expressRelation.orderDetail', 'order')->one();

            if (!$express) {
                throw new \Exception('物流不存在');
            }

            $cityInfo = json_decode($express->city_info, true);
            $cityService = CityService::find()->andWhere(['id' => $cityInfo['city_info']['id'], 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();
            if (!$cityService) {
                throw new \Exception('配送商家不存在');
            }

            $deliveryForm = new DeliveryForm();
            $delivery = $deliveryForm->getDeliveryData();

            $form = new CityServiceForm();
            $corporation = $form->getCorporation($cityService->distribution_corporation);
            $otherData = $this->getOtherData($express->express_type, $cityInfo, $express);

            $location = explode(',', $express->order->location);
            $data = [
                'goods_num' => 0,
                'goods_pic' => '',
                'goods_name' => '',
                'status' => $express->status,
                'status_text' => $otherData['statusText'],
                'city_name' => $express->city_name,
                'city_mobile' => $express->city_mobile,
                'corporation_name' => $corporation['name'],
                'corporation_icon' => $corporation['icon'],
                'estimate_time' => $otherData['estimateTime'],
                'user_longitude' => isset($location[0]) ? $location[0] : 0,
                'user_latitude' => isset($location[1]) ? $location[1] : 0,
                'man_longitude' => $otherData['manLongitude'],
                'man_latitude' => $otherData['manLatitude'],
                'shop_longitude' => $delivery['address']['longitude'],
                'shop_latitude' => $delivery['address']['latitude'],
            ];

            foreach ($express->expressRelation as $key => $item) {
                $data['goods_num'] += $item->orderDetail->num;
                $goodsInfo = json_decode($item->orderDetail->goods_info, true);
                if ($key == 0) {
                    $data['goods_pic'] = $goodsInfo['goods_attr']['cover_pic'];
                    $data['goods_name'] = $goodsInfo['goods_attr']['name'];
                }
            }

            return $data;

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    private function getOtherData($express_type, $cityInfo, $express)
    {
        switch ($express_type) {
            case '微信':
                $deliveryId = 'WECHAT';
                break;
            case '同城配送':
                $deliveryId = 'CITY';
                break;
            case '顺丰同城急送':
                $deliveryId = 'SFTC';
                break;
            case '闪送':
                $deliveryId = 'SC';
                break;
            case '达达':
                $deliveryId = 'DADA';
                break;
            case '美团配送':
                $deliveryId = 'MTPS';
                break;
            default:
                throw new \Exception('订单物流数据异常');
                break;
        }

        $manLongitude = 0;
        $manLatitude = 0;
        switch ($express->status) {
            case 101:
                $statusText = '等待分配骑手';
                break;
            case 102:
                $statusText = '配送员正在前往商家';
                $data = $this->getStatusAddress($deliveryId, $cityInfo, 102);
                $manLongitude = $data['manLongitude'];
                $manLatitude = $data['manLatitude'];
                break;
            case 202:
                $statusText = '配送员已取货';
                $data = $this->getStatusAddress($deliveryId, $cityInfo, 202);
                $manLongitude = $data['manLongitude'];
                $manLatitude = $data['manLatitude'];
                break;
            case 301:
                $statusText = '配送员已取货，配送中';
                $data = $this->getStatusAddress($deliveryId, $cityInfo, 301);
                $manLongitude = $data['manLongitude'];
                $manLatitude = $data['manLatitude'];
                break;
            case 302:
                $statusText = '配送完成';
                $data = $this->getStatusAddress($deliveryId, $cityInfo, 302);
                $manLongitude = $data['manLongitude'];
                $manLatitude = $data['manLatitude'];
                break;
            default:
                $statusText = '配送物流未知';
                break;
        }

        $estimateTime = '';
        if ($express->status >= 301) {
            $estimateTime = $this->getEstimateTime($deliveryId, $cityInfo, 301);
        }

        return [
            'manLongitude' => $manLongitude,
            'manLatitude' => $manLatitude,
            'estimateTime' => $estimateTime,
            'statusText' => $statusText,
        ];
    }

    private function getStatusAddress($deliveryId, $cityInfo, $status)
    {
        $data = [
            'manLongitude' => 0,
            'manLatitude' => 0,
        ];

        if (isset($cityInfo[$status])) {
            if ($deliveryId == 'SFTC') {
                $data['manLongitude'] = $cityInfo[$status]['rider_lng'];
                $data['manLatitude'] = $cityInfo[$status]['rider_lat'];
            } elseif ($deliveryId == 'WECHAT') {
                $data['manLongitude'] = $cityInfo[$status]['agent']['lng'];
                $data['manLatitude'] = $cityInfo[$status]['agent']['lat'];
            } elseif ($deliveryId == 'DADA') {
                // 达达没有经纬度
            } elseif ($deliveryId == 'SS') {
                // 达达没有经纬度
            } elseif ($deliveryId == 'CITY') {
                // TODO
            }
        }

        return $data;
    }

    // 预计时间
    private function getEstimateTime($deliveryId, $cityInfo, $status)
    {
        $estimateTime = '';
        if ($deliveryId == 'SFTC') {
            // 顺丰没有预计完成时间
            $estimateTime = '';
        } elseif ($deliveryId == 'WECHAT') {
            $reachTime = date('H:i', $cityInfo[$status]['agent']['reach_time']);
            $estimateTime = '预计' . $reachTime . '送达';
        } elseif ($deliveryId == 'DADA') {
            // 达达没有预计送达时间
        }elseif ($deliveryId == 'SS') {
            // 达达没有预计送达时间
        } elseif ($deliveryId == 'CITY') {
            // TODO
        }

        return $estimateTime;
    }
}
