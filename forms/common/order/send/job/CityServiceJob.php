<?php

namespace app\forms\common\order\send\job;

use yii\base\Component;
use yii\queue\JobInterface;

class CityServiceJob extends Component implements JobInterface
{
    public $shopOrderId;
    public $waybillId;
    public $status;
    public $instance;

    public function execute($queue)
    {
        try {
            \Yii::warning('微信模拟配送开始');
            $result = $this->instance->mockUpdateOrder([
                'shop_order_id' => $this->shopOrderId,
                'action_time' => time(),
                'order_status' => $this->status,
                'action_msg' => '模拟测试',
                'waybill_id' => $this->waybillId,
            ]);
            \Yii::warning('微信模拟配送是否成功' . $result->isSuccessful());
        } catch (\Exception $e) {
            \Yii::error('微信模拟配送失败');
            \Yii::error($e);
        }
    }
}
