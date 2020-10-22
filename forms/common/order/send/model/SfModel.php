<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\model\BaseModel;

class SfModel extends BaseModel
{
    public $data;
    public $cityPreviewOrder;
    public $debug;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        $this->setDebugData();
        $data = $this->data;

        return [
            'user_lng' => $data['receiver']['lng'],
            'user_lat' => $data['receiver']['lat'],
            'user_address' => $data['receiver']['address_detail'],
            'weight' => $data['cargo']['goods_weight'] > 0 ? $data['cargo']['goods_weight'] * 1000 : 1000,
            'product_type' => $data['product_type'] ?: 99,
            'is_appoint' => 0,
            'pay_type' => 1,
            'is_insured' => 0,
            'is_person_direct' => 0,
            'push_time' => time(),
        ];
    }

    public function getAddOrder()
    {
        // 下单时数据要用 预下单时的数据
        $this->data = $this->cityPreviewOrder->all_order_info;
        $this->setDebugData();
        $data = $this->data;

        return [
            'shop_order_id' => $data['shop_order_id'],
            'order_source' => $data['shop_no'],
            'pay_type' => 1,
            'order_time' => time(),
            'is_appoint' => 0,
            'is_insured' => 0,
            'is_person_direct' => 0,
            'return_flag' => 511,
            'push_time' => time(),
            'version' => 1,
            'receive' => [
                'user_name' => $data['receiver']['name'],
                'user_phone' => $data['receiver']['phone'],
                'user_address' => $data['receiver']['address_detail'],
                'user_lng' => $data['receiver']['lng'],
                'user_lat' => $data['receiver']['lat'],
            ],
            'order_detail' => [
                'total_price' => $data['cargo']['goods_value'] * 100,
                'product_type' => $data['product_type'] ?: 99,
                'weight_gram' => $data['cargo']['goods_weight'] > 0 ? $data['cargo']['goods_weight'] * 1000 : 1000,
                'product_num' => $data['shop']['goods_count'],
                'product_type_num' => 1,
            ],
        ];
    }

    private function setDebugData()
    {
        // 测试环境参数
        if ($this->debug) {
            $this->data['receiver']['lng'] = '116.334424';
            $this->data['receiver']['lat'] = '40.030177';
            $this->data['receiver']['address_detail'] = '北京北京市海淀区华润五彩城购物中心';
        }
    }
}
