<?php


namespace app\plugins\stock\events;


use app\models\Order;
use yii\base\Event;

class OrderEvent extends Event
{
    /** @var Order */
    public $order;
}
