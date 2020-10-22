<?php

namespace app\forms\common\order\send\model;

use GuzzleHttp\Client;

abstract class BaseModel
{
    // abstract public function getPreAddOrder();
    // abstract public function getAddOrder();
    /**
     * 根据经纬度 获取城市名
     * @param  [type] $lng [description]
     * @param  [type] $lat [description]
     * @return [type]      [description]
     */
    protected function getAddressInfo($lng, $lat)
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
}
