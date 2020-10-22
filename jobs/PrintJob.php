<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/8
 * Time: 9:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\forms\common\prints\PrintOrder;
use app\models\Mall;
use app\models\Order;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class PrintJob
 * @package app\jobs
 * @property Order $order
 * @property Mall $mall
 */
class PrintJob extends BaseObject implements JobInterface
{
    public $order;
    public $mall;
    public $orderType;

    public function execute($queue)
    {
        try {
            \Yii::$app->setMall($this->mall);

            $printer = new PrintOrder();
            $printer->print($this->order, $this->order->id, $this->orderType);
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
            \Yii::warning($exception);
        }
    }
}
