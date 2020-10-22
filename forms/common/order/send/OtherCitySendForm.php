<?php

namespace app\forms\common\order\send;

use CityService\Factory;
use GuzzleHttp\Client;
use app\core\response\ApiCode;
use app\forms\common\order\send\BaseSend;
use app\forms\common\order\send\job\CityServiceJob;
use app\forms\common\order\send\job\DadaCityServiceJob;
use app\forms\common\order\send\model\DadaModel;
use app\forms\common\order\send\model\MtModel;
use app\forms\common\order\send\model\SfModel;
use app\forms\common\order\send\model\SsModel;
use app\forms\common\order\send\model\WechatModel;
use app\forms\mall\city_service\CityServiceForm;
use app\forms\mall\delivery\DeliveryForm;
use app\models\CityPreviewOrder;
use app\models\CityService;
use app\models\OrderDetailExpress;
use app\models\UserInfo;
use app\plugins\wxapp\models\WxappConfig;
use yii\helpers\ArrayHelper;

class OtherCitySendForm extends BaseSend
{
    public $city_service;
    public $is_preview;
    public $delivery_no;

    private $order;
    private $currentCityService;
    private $deliveryId;
    private $corporationName;
    private $cityPreviewOrder;

    private $isDebug = false; // 是否为测试;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['city_service', 'is_preview'], 'required'],
            [['city_service', 'delivery_no'], 'string'],
            [['is_preview'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'city_service' => '配送名称',
            'is_preview' => '是否预下单',
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $this->isDebug = env('CITY_SERVICE_IS_ONLINE') ? true : false;

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            // 暂不支持第三方修改配送
            if ($this->express_id) {
                $orderDetailExpress = OrderDetailExpress::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->express_id,
                ])->one();

                if ($orderDetailExpress->send_type == 1) {
                    throw new \Exception('第三方配送暂不支持修改配送员');
                }
            }

            $order = $this->getOrder();
            $this->order = $order;
            $this->checkCityService();

            // 第三方配送 预下单
            if ($this->is_preview != 0) {
                $preAddOrder = $this->preAddOrder();
                $transaction->commit();

                return $preAddOrder;
            }

            // 正式下单
            $this->saveOrderDetailExpress($order);
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功',
            ];

        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    public function saveExtraData($orderDetailExpress)
    {
        $cityService = $this->currentCityService;
        $cityInfo = [
            'city_info' => [
                'id' => $cityService->id,
                'name' => $cityService->name,
                'shop_no' => $cityService->shop_no,
            ],
            'city_service_info' => ArrayHelper::toArray($this->currentCityService),
        ];

        $orderDetailSign = $this->getOrderDetailSign();
        $this->cityPreviewOrder = CityPreviewOrder::findOne(['order_detail_sign' => $orderDetailSign]);
        if (!$this->cityPreviewOrder) {
            throw new \Exception('预下单数据不存在');
        }
        $this->cityPreviewOrder->order_info = json_decode($this->cityPreviewOrder->order_info, true);
        $this->cityPreviewOrder->result_data = json_decode($this->cityPreviewOrder->result_data, true);
        $this->cityPreviewOrder->all_order_info = json_decode($this->cityPreviewOrder->all_order_info, true);

        // 下单 确认发货
        if ($this->currentCityService->service_type == '微信') {
            $data = $this->cityPreviewOrder->all_order_info;
        } else {
            // 第三方
            $data = $this->getAddOrderInfo();
        }

        $instance = $this->getInstance();
        $result = $instance->addOrder($data);

        if (!$result->isSuccessful()) {
            throw new \Exception($result->getMessage());
        }

        $res = $this->getResponse($result->getOriginalData(), 'addOrder');
        $cityInfo['result'] = $res;
        $orderDetailExpress->shop_order_id = $data['shop_order_id'];
        $orderDetailExpress->status = 101;

        $this->debugTest($data['shop_order_id'], isset($res['waybill_id']) ? $res['waybill_id'] : 0);
        $this->dadaDebugTest($data['shop_order_id'], $this->corporationName);

        $orderDetailExpress->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
        $orderDetailExpress->city_name = '';
        $orderDetailExpress->city_mobile = '';
        $orderDetailExpress->send_type = 1;
        $orderDetailExpress->city_service_id = $cityService->id;

        $orderDetailExpress->express_type = $this->currentCityService->service_type == '微信' ? '微信' : $this->corporationName;
    }

    private function checkCityService()
    {
        // 从字符串中截取配送商家
        $id = substr($this->city_service, 1, strpos($this->city_service, ')') - 1);
        $cityService = CityService::find()->andWhere(['id' => $id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();
        if (!$cityService) {
            throw new \Exception('所选配送商家不存在');
        }

        $this->currentCityService = $cityService;

        $form = new CityServiceForm();
        $this->deliveryId = $form->getDeliveryId($this->currentCityService->distribution_corporation);

        $this->corporationName = $form->getCorporationName($this->currentCityService->distribution_corporation);

        return $cityService;
    }

    private function preAddOrder()
    {
        $instance = $this->getInstance();
        $allOrderInfo = $this->getOrderInfo();
        $orderInfo = [];

        if ($this->currentCityService->service_type == '微信') {
            $wechatModel = new WechatModel();
            $wechatModel->data = $allOrderInfo;
            $wechatModel->deliveryId = $this->deliveryId;
            $allOrderInfo = $wechatModel->getPreAddOrder();
            $result = $instance->preAddOrder($allOrderInfo);
        } else {
            // 第三方
            $orderInfo = $this->getPreAddOrderInfo($allOrderInfo, $instance);
            $result = $instance->preAddOrder($orderInfo);
        }

        if (!$result->isSuccessful()) {
            throw new \Exception($result->getMessage());
        }

        $res = $this->getResponse($result->getOriginalData(), 'preAddOrder');

        $resultdata = [];
        $resultdata['preview_success'] = 1;
        $resultdata['name'] = $this->corporationName;

        $resultdata = array_merge($resultdata, $res);

        $orderDetailSign = $this->getOrderDetailSign();
        $cityPreviewOrder = CityPreviewOrder::findOne(['order_detail_sign' => $orderDetailSign]);

        if (!$cityPreviewOrder) {
            $cityPreviewOrder = new CityPreviewOrder();
        }

        $cityPreviewOrder->result_data = json_encode($resultdata, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->order_info = json_encode($orderInfo, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->all_order_info = json_encode($allOrderInfo, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->order_detail_sign = $orderDetailSign;
        $previewOrderResult = $cityPreviewOrder->save();

        if (!$previewOrderResult) {
            throw new \Exception($this->getErrorMsg($previewOrderResult));
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '预下单成功',
            'data' => $resultdata,
        ];
    }

    private function getInstance()
    {
        $data = json_decode($this->currentCityService->data);

        $service_type = $this->currentCityService->service_type;

        if ($service_type == '第三方') {
            // 单独对接配送公司
            switch ($this->deliveryId) {
                // 顺丰
                case 'SFTC':
                    $divers = 'sf';
                    $config = [
                        'dev_id' => $data->appkey,
                        'dev_key' => $data->appsecret,
                        'shop_id' => $this->currentCityService->shop_no,
                    ];
                    break;
                // 闪送
                case 'SS':
                    $divers = 'ss';
                    $config = [
                        'clientId' => $data->appkey,
                        'secret' => $data->appsecret,
                        'shopId' => $this->currentCityService->shop_no,
                        'debug' => $this->isDebug ? true : false,
                    ];
                    break;
                // 达达
                case 'DADA':
                    $divers = 'dada';
                    $config = [
                        'appKey' => $data->appkey,
                        'appSecret' => $data->appsecret,
                        'sourceId' => $data->shop_id,
                        'debug' => $this->isDebug ? true : false,
                    ];
                    break;
                // 美团
                case 'MTPS':
                    $divers = 'mt';
                    $config = [
                        'appKey' => $data->appkey,
                        'appSecret' => $data->appsecret,
                        'shopId' => $this->currentCityService->shop_no,
                    ];
                    break;
                default:
                    throw new \Exception('未知配送公司1');
                    break;
            }
        } elseif ($service_type == '微信') {
            // 腾讯配送接口
            $wxappConfig = WxappConfig::find()
                ->where(['mall_id' => \Yii::$app->mall->id])
                ->with(['service'])
                ->one();
            if (!$wxappConfig) {
                throw new \Exception('微信参数未配置');
            }

            $divers = 'wechat';
            $config = [
                'appId' => $wxappConfig->appid,
                'appSecret' => $wxappConfig->appsecret,
                'deliveryId' => $this->isDebug ? 'TEST' : $this->deliveryId, // 是否为测试
                'shopId' => $data->appkey,
                'deliveryAppSecret' => $data->appsecret,
            ];
        } else {
            throw new \Exception('未知接口类型');
        }

        $instance = Factory::getInstance($divers, $config);

        return $instance;
    }

    /**
     * 注意！！！
     * 预下单调用此方法并且会存入数据库  正式下单时应去查询数据库
     * @return [type] [description]
     */
    private function getOrderInfo()
    {
        $goodsList = [];
        $goodsValue = 0;
        $goodsWeight = 0;
        $goodsCount = 0;
        $goodsName = '';
        $goodsImageUrl = '';
        $userInfo = UserInfo::findOne(['user_id' => $this->order->user_id]);
        $shopOrderId = $this->order->getOrderNo('TC'); // 用于生成唯一订单号
        foreach ($this->order->detail as $key => $value) {
            foreach ($this->order_detail_id as $detailId) {
                if ($value['id'] == $detailId) {
                    $goodsValue += $value->total_price;
                    $goodsInfo = json_decode($value->goods_info);
                    $goodsWeight += $goodsInfo->goods_attr->weight / 1000;
                    $goodsCount += $value->num;
                    $goodsName .= $goodsInfo->goods_attr->name . ' ';
                    if (!$goodsImageUrl) {
                        $goodsImageUrl = $goodsInfo->goods_attr->cover_pic;
                    }
                    $goodsList[] = [
                        'good_count' => $value->num,
                        'good_name' => $goodsInfo->goods_attr->name,
                        'good_price' => $value->unit_price,
                        'good_no' => $value->goods_no,
                        'good_unit' => $value->goods->goodsWarehouse->unit ?: '件',
                    ];
                }
            }
        }

        $address = explode(' ', $this->order->address);
        $location = explode(',', $this->order->location);

        $form = new DeliveryForm();
        $delivery = $form->getDeliveryData();

        $cityServiceData = json_decode($this->currentCityService->data, true);

        $cargoFirstClass = '';
        $cargoSecondClass = '';
        if (isset($cityServiceData['wx_product_type'])) {
            $wxProductType = json_decode($cityServiceData['wx_product_type'], true);
            $cargoFirstClass = isset($wxProductType[0]) ? $wxProductType[0] : '';
            $cargoSecondClass = isset($wxProductType[1]) ? $wxProductType[1] : '';
        }

        $data = [
            "cargo" => [
                "cargo_first_class" => $cargoFirstClass ?: "其它",
                "cargo_second_class" => $cargoSecondClass ?: "其它",
                "goods_detail" => [
                    "goods" => $goodsList,
                ],
                "goods_value" => $goodsValue,
                "goods_weight" => number_format($goodsWeight, 2), // 单位kg
            ],
            "openid" => $userInfo->platform_user_id,
            "order_info" => [
                "order_time" => time(),
                "order_type" => 0,
                "poi_seq" => $this->order->order_no,
            ],
            "receiver" => [
                "address" => isset($address[0]) ? $address[0] : '',
                "address_detail" => isset($address[1]) ? $address[1] : '',
                "city" => $this->getAddressInfo(isset($location[0]) ? $location[0] : 0, isset($location[1]) ? $location[1] : 0)['city'],
                "lat" => isset($location[1]) ? $location[1] : 0,
                "lng" => isset($location[0]) ? $location[0] : 0,
                "name" => $this->order->name,
                "phone" => $this->order->mobile,
            ],
            "sender" => [
                "address" => $delivery['address']['address'],
                "address_detail" => $delivery['address']['address'],
                "city" => $this->getAddressInfo($delivery['address']['longitude'], $delivery['address']['latitude'])['city'],
                "lat" => $delivery['address']['latitude'],
                "lng" => $delivery['address']['longitude'],
                "name" => \Yii::$app->mall->name,
                "phone" => $delivery['contact_way'],
            ],
            "shop" => [
                "goods_count" => $goodsCount,
                "goods_name" => $goodsName,
                "img_url" => $goodsImageUrl,
                "wxa_path" => "/page/order/index",
            ],
            "shop_no" => $this->currentCityService->shop_no,
            "shop_order_id" => $shopOrderId,
            'product_type' => isset($cityServiceData['product_type']) ? $cityServiceData['product_type'] : ''
        ];

        return $data;
    }

    /**
     * 根据经纬度 获取城市名
     * @param  [type] $lng [description]
     * @param  [type] $lat [description]
     * @return [type]      [description]
     */
    private function getAddressInfo($lng, $lat)
    {
        $url = $url = 'https://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=OV7BZ-ZT3HP-6W3DE-LKHM3-RSYRV-ULFZV';
        $client = new Client();
        $res = $client->request('GET', $url, []);

        if ($res->getStatusCode() != 200) {
            throw new \Exception('用户收货地址异常1');
        }

        $data = json_decode($res->getBody(), true);
        if (!isset($data['result']['address_component']['city'])) {
            throw new \Exception('用户收货地址异常2');
        }
        $city = $data['result']['address_component']['city'];
        $new_city = substr($city, 0, strlen($city) - 3);

        return [
            'city' => $city,
            'new_city' => $new_city,
        ];
    }

    private function debugTest($shopOrderId, $waybillId)
    {
        // debug模式 开启模拟测试
        if (env('YII_DEBUG')) {
            // 分配骑手
            \Yii::$app->queue->delay(10)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 102,
                'instance' => $this->getInstance(),
            ]));
            // 骑手取货
            \Yii::$app->queue->delay(20)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 202,
                'instance' => $this->getInstance(),
            ]));
            // 配送中
            \Yii::$app->queue->delay(30)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 301,
                'instance' => $this->getInstance(),
            ]));
            // 配送完成
            \Yii::$app->queue->delay(40)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 302,
                'instance' => $this->getInstance(),
            ]));
        }
    }

    private function dadaDebugTest($shopOrderId, $corporationName)
    {
        if ($corporationName != '达达') {
            return false;
        }
        \Yii::warning('达达模拟配送开始');

        // debug模式 开启模拟测试
        if (env('YII_DEBUG')) {
            // 分配骑手
            \Yii::$app->queue->delay(10)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'accept',
                'instance' => $this->getInstance(),
            ]));
            // 骑手取货
            \Yii::$app->queue->delay(20)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'fetch',
                'instance' => $this->getInstance(),
            ]));
            // 配送完成
            \Yii::$app->queue->delay(30)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'finish',
                'instance' => $this->getInstance(),
            ]));
        }
    }

    // 该sign 为order_detail_id 想拼接 转md5
    private function getOrderDetailSign()
    {
        $string = implode(',', $this->order_detail_id);
        return md5($string);
    }

    /**
     * 统一处理返回值
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    private function getResponse($response, $type)
    {
        $array = [];

        if ($this->currentCityService->service_type == '微信') {
            if ($type == 'preAddOrder') {
                $array = $response;
                $array['fee'] = number_format($response['fee'], 2);
            } else if ($type == 'addOrder') {
                $array = $response;
            }
        } else {
            // 第三方
            switch ($this->deliveryId) {
                // 顺丰
                case 'SFTC':
                    if ($type == 'preAddOrder') {
                        $array = $response['result'];
                        $array['fee'] = number_format($response['result']['total_price'] / 100, 2);
                    } else if ($type == 'addOrder') {
                        $array = $response;
                    }
                    break;
                // 闪送
                case 'SS':
                    if ($type == 'preAddOrder') {
                        $array = $response['data'];
                        $array['fee'] = number_format($response['data']['totalFeeAfterSave'] / 100, 2);
                    } else if ($type == 'addOrder') {
                        $array = $response;
                    }
                    break;
                // 达达
                case 'DADA':
                    if ($type == 'preAddOrder') {
                        $array = $response['result'];
                        $array['fee'] = number_format($response['result']['fee'], 2);
                    } else if ($type == 'addOrder') {
                        $array = $response;
                    }

                    break;
                // 美团
                case 'MTPS':
                    if ($type == 'preAddOrder') {
                        $array = $response['data'];
                        $array['fee'] = 0;
                    } else if ($type == 'addOrder') {
                        $array = $response;
                    }
                    break;
                default:
                    throw new \Exception('未知配送公司2');
                    break;
            }
        }

        return $array;
    }

    private function getPreAddOrderInfo($orderInfo, $instance)
    {
        $data = [];
        switch ($this->deliveryId) {
            // 顺丰
            case 'SFTC':
                $sfModel = new SfModel();
                $sfModel->data = $orderInfo;
                $sfModel->debug = $this->isDebug;

                $data = $sfModel->getPreAddOrder();
                break;
            // 闪送
            case 'SS':
                $ssModel = new SsModel();
                $ssModel->data = $orderInfo;
                $ssModel->debug = $this->isDebug;

                $data = $ssModel->getPreAddOrder();
                break;
            // 达达
            case 'DADA':
                $dadaModel = new DadaModel();
                $dadaModel->data = $orderInfo;
                $dadaModel->instance = $instance;

                $data = $dadaModel->getPreAddOrder();
                break;
            // 美团
            case 'MTPS':
                $mtModel = new MtModel();
                $mtModel->data = $orderInfo;
                $data = $mtModel->getPreAddOrder();
                break;
            default:
                throw new \Exception('未知配送公司2');
                break;
        }

        return $data;
    }

    /**
     * 获取下单数据
     * 部分数据需要使用预下单数据
     * @return [type] [description]
     */
    private function getAddOrderInfo()
    {
        $data = [];

        switch ($this->deliveryId) {
            // 顺丰
            case 'SFTC':
                $sfModel = new SfModel();
                $sfModel->cityPreviewOrder = $this->cityPreviewOrder;
                $sfModel->debug = $this->isDebug;

                $data = $sfModel->getAddOrder();
                break;
            // 闪送
            case 'SS':
                $ssModel = new SsModel();
                $ssModel->cityPreviewOrder = $this->cityPreviewOrder;
                $ssModel->debug = $this->isDebug;

                $data = $ssModel->getAddOrder();
                break;
            // 达达
            case 'DADA':
                $dadaModel = new DadaModel();
                $dadaModel->cityPreviewOrder = $this->cityPreviewOrder;

                $data = $dadaModel->getAddOrder();
                break;
            // 美团
            case 'MTPS':
                $mtModel = new MtModel();
                $mtModel->cityPreviewOrder = $this->cityPreviewOrder;

                $data = $mtModel->getAddOrder();
                break;
            default:
                throw new \Exception('未知配送公司2');
                break;
        }

        return $data;
    }
}
