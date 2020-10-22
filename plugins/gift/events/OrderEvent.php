<?php


namespace app\plugins\gift\events;


class OrderEvent extends \app\events\OrderEvent
{
    public $type = 1; // 1--收礼物 2--买礼物
}
