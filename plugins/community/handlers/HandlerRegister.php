<?php


namespace app\plugins\community\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    const COMMUNITY_SUCCESS = 'community_success';

    public function getHandlers()
    {
        return [
            OrderSalesHandler::class,
            OrderCanceledHandler::class,
            SuccessHandler::class,
            OrderRefundConfirmedHandler::class,
        ];
    }
}
