<?php


namespace app\plugins\pick\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            GoodsDestroyHandler::class,
        ];
    }
}
