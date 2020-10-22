<?php

namespace app\forms\common\order\send\job;

use yii\base\Component;
use yii\queue\JobInterface;

class DadaCityServiceJob extends Component implements JobInterface
{
    public $shopOrderId;
    public $mock_type;
    public $instance;

    public function execute($queue)
    {
        try {
            \Yii::warning('达达模拟配送开始');
            $instance = $this->instance;

            $result = $instance->mockUpdateOrder(
                [
                    'shop_order_id' => $this->shopOrderId,
                ],
                [
                    'mock_type' => $this->mock_type,
                ]);
            \Yii::warning('达达模拟配送是否成功' . $result->isSuccessful());
        } catch (\Exception $e) {
            \Yii::error('达达模拟配送失败');
            \Yii::error($e);
        }
    }
}
