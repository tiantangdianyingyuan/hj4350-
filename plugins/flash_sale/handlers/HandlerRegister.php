<?php

namespace app\plugins\flash_sale\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            GoodsDestroyHandler::class,
            OrderCreatedHandler::class
        ];
    }
}
