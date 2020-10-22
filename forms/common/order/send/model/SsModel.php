<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\model\BaseModel;

class SsModel extends BaseModel
{
    public $data;
    public $debug;
    public $cityPreviewOrder;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        $data = $this->data;

        $data['order_info']['is_direct_delivery'] = 1;

        $this->dealPos($data);
        $receiver = [
            "orderNo" => $data['shop_order_id'],
            "toAddress" => $data['receiver']['address'] ?? '',
            "toAddressDetail" => $data['receiver']['address_detail'],
            "toLatitude" => $data['receiver']['lat'],
            "toLongitude" => $data['receiver']['lng'],
            "toReceiverName" => $data['receiver']['name'],
            "toMobile" => $data['receiver']['phone'],
            "goodType" => $data['product_type'] ?: 10,
            "weight" => $data['cargo']['goods_weight'] > 1 ? $data['cargo']['goods_weight'] : 1,
        ];

        $json = [
            "cityName" => $data['sender']['city'],
            "sender" => [
                "fromAddress" => $data['sender']['address'],
                "fromAddressDetail" => $data['sender']['address_detail'],
                "fromSenderName" => $data['sender']['name'],
                "fromMobile" => $data['sender']['phone'],
                "fromLatitude" => $data['sender']['lat'],
                "fromLongitude" => $data['sender']['lng'],
            ],
            "receiverList" => [
                $receiver,
            ],
            "appointType" => 0,
        ];

        return [
            'data' => json_encode($json, JSON_UNESCAPED_UNICODE),
        ];
    }

    public function getAddOrder()
    {
        $array = [
            'issOrderNo' => $this->cityPreviewOrder->result_data['orderNumber'],
        ];

        return [
            'shop_order_id' => $this->cityPreviewOrder->all_order_info['shop_order_id'],
            'data' => json_encode($array, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * 腾讯地图---->百度地图
     * @param double $lat 纬度
     * @param double $lng 经度
     */
    private function Convert_GCJ02_To_BD09($lat, $lng)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta) + 0.0065;
        $lat = $z * sin($theta) + 0.006;
        return array('lng' => $lng, 'lat' => $lat);
    }

    /**
     * 转换腾讯地图坐标为百度坐标
     * @param $data
     */
    private function dealPos(&$data)
    {
        $receiverPos = $this->Convert_GCJ02_To_BD09($data['receiver']['lat'], $data['receiver']['lng']);
        $data['receiver']['lat'] = $receiverPos['lat'];
        $data['receiver']['lng'] = $receiverPos['lng'];
        $senderPos = $this->Convert_GCJ02_To_BD09($data['sender']['lat'], $data['sender']['lng']);
        $data['sender']['lat'] = $senderPos['lat'];
        $data['sender']['lng'] = $senderPos['lng'];
    }
}
