<?php


namespace app\plugins\advance\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            GoodsEditHandler::class,
            GoodsDestroyHandler::class,
            DepositOrderRefundHandler::class,
            OrderRefundHandler::class,
            OrderCreatedHandler::class,
            OrderCanceledHandler::class,
        ];
    }
}
