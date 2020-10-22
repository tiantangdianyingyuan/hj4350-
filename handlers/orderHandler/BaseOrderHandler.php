<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 13:31
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;


use app\events\OrderEvent;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\share\AddShareOrder;
use app\jobs\PrintJob;
use app\models\Mall;
use app\models\Model;
use app\forms\OrderConfig;

/**
 * @property OrderEvent $event
 * @property Mall $mall
 * @property OrderConfig $orderConfig
 */
abstract class BaseOrderHandler extends Model
{
    public $event;
    public $mall;
    public $orderConfig;

    /**
     * @return mixed
     * 事件处理
     */
    abstract public function handle();

    /**
     * @return $this
     */
    public function setMall()
    {
        try {
            $this->mall = \Yii::$app->mall;
        } catch (\Exception $exception) {
            $mall = Mall::findOne(['id' => $this->event->order->mall_id]);
            \Yii::$app->setMall($mall);
            $this->mall = \Yii::$app->mall;
        }
        return $this;
    }

    /**
     * @param string $orderType order|pay|confirm 打印方式
     * @return $this
     * 小票打印
     */
    protected function receiptPrint($orderType)
    {
        try {
            if ($this->orderConfig->is_print != 1) {
                throw new \Exception($this->event->order->sign . '未开启小票打印');
            }
            $job = new PrintJob();
            $job->mall = \Yii::$app->mall;
            $job->order = $this->event->order;
            $job->orderType = $orderType;
            \Yii::$app->queue->delay(0)->push($job);
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        }
        return $this;
    }

    protected function addShareOrder()
    {
        try {
            (new AddShareOrder())->save($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('分销佣金记录失败：' . $exception->getMessage());
            \Yii::error($exception);
        }
        return $this;
    }

    public function setMchId()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        return $this;
    }

    public function setTypeData()
    {
        CommonEcard::getCommon()->setTypeData($this->event->order);
    }
}
