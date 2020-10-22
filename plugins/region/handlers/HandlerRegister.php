<?php


namespace app\plugins\region\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderSalesHandler::class,
            BecomeRegionHandler::class,
            ChangeShareMemberHandler::class,
            RemoveRegionHandler::class,
            LevelUpRegionHandler::class
        ];
    }
}
