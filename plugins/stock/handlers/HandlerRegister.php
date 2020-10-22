<?php


namespace app\plugins\stock\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderSalesHandler::class,
            BecomeStockHandler::class,
            ChangeShareMemberHandler::class,
            RemoveStockHandler::class
        ];
    }
}
