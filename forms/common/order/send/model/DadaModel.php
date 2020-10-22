<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\model\BaseModel;

class DadaModel extends BaseModel
{
    public $data;
    public $instance;
    public $cityPreviewOrder;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        $data = $this->data;
        $instance = $this->instance;

        $cityName = $this->getAddressInfo($data['receiver']['lng'], $data['receiver']['lat'])['new_city'];
        $response = $instance->getCityCodeList();
        $cityCode = '';

        if (!$response->isSuccessful()) {
            throw new \Exception($response->getMessage());
        }

        $resultData = $response->getOriginalData();

        foreach ($resultData['result'] as $key => $item) {
            if ($item['cityName'] == $cityName) {
                $cityCode = $item['cityCode'];
            }
        }

        if (!$cityCode) {
            throw new \Exception($cityName . '不支持配送');
        }

        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/msg-notify/dada-city-service.php';

        $productList = [];
        foreach ($data['cargo']['goods_detail']['goods'] as $key => $item) {
            $productList[] = [
                'sku_name' => $item['good_name'],
                'src_product_no' => $item['good_no'] ?: '',
                'count' => number_format($item['good_count'], 2),
            ];
        }

        return [
            'shop_no' => $data['shop_no'],
            'origin_id' => $data['shop_order_id'],
            'city_code' => $cityCode,
            'cargo_price' => $data['cargo']['goods_value'],
            'is_prepay' => 0,
            'receiver_name' => $data['receiver']['name'],
            'receiver_phone' => $data['receiver']['phone'],
            'receiver_address' => $data['receiver']['address'] . $data['receiver']['address_detail'],
            'callback' => $url,
            'cargo_weight' => $data['cargo']['goods_weight'] > 0 ? doubleval($data['cargo']['goods_weight']) : 1,
            'product_list' => $productList,
        ];
    }

    public function getAddOrder()
    {
        return [
            'deliveryNo' => $this->cityPreviewOrder->result_data['deliveryNo'],
            'shop_order_id' => $this->cityPreviewOrder->order_info['origin_id'],
        ];
    }
}
