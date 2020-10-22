<?php


namespace app\plugins\gift\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderCanceledHandler::class,
        ];
    }
}
