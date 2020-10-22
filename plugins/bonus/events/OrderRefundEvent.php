<?php


namespace app\plugins\bonus\events;


use app\models\OrderRefund;
use yii\base\Event;

/**
 * @property OrderRefund $order_refund
 */
class OrderRefundEvent extends Event
{
    public $order_refund;
}
