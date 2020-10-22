<?php


namespace app\plugins\bonus\events;


use app\models\Order;
use yii\base\Event;

class OrderEvent extends Event
{
    /** @var Order */
    public $order;

    public $cartIds = [];
}
