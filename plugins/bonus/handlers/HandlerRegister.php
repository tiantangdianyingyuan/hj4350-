<?php


namespace app\plugins\bonus\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderCanceledHandler::class,
            OrderPayedHandler::class,
            OrderRefundConfirmedHandler::class,
            OrderSalesHandler::class,
            BecomeCaptainHandler::class,
            BecomeShareHandler::class,
            ChangeShareMemberHandler::class,
            MemberLevelHandler::class,
        ];
    }
}
