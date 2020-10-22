<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/8/13
 * Time: 16:00
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;


use app\events\OrderEvent;
use app\forms\common\order\CommonOrder;
use app\models\Order;

class OrderChangePriceHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CHANGE_PRICE, function ($event) {
            /** @var OrderEvent $event */
            $commonOrder = CommonOrder::getCommonOrder($event->order->sign);
            $orderHandler = $commonOrder->getOrderHandler();
            $handler = $orderHandler->orderChangePriceHandlerClass;
            $handler->orderConfig = $commonOrder->getOrderConfig();
            $handler->event = $event;
            $handler->setMchId()->setMall()->handle();
        });
    }
}
