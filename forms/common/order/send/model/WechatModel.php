<?php

namespace app\forms\common\order\send\model;

use app\forms\common\order\send\model\BaseModel;

class WechatModel extends BaseModel
{
    public $data;
    public $deliveryId;

    /**
     * 预下单数据
     * @return [type] [description]
     */
    public function getPreAddOrder()
    {
        switch ($this->deliveryId) {
            // 顺丰
            case 'SFTC':

                break;
            // 闪送
            case 'SS':
                $this->data['order_info']['is_direct_delivery'] = 1;
                break;
            // 达达
            case 'DADA':

                break;
            // 美团
            case 'MTPS':
                $this->data['order_info']['delivery_service_code'] = 4002;
                break;
            default:
                throw new \Exception('微信配送，未知配送公司');
                break;
        }

        return $this->data;
    }

    public function getAddOrder()
    {
        return $this->data;
    }
}
