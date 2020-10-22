<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/11
 * Time: 10:18
 */

namespace app\plugins\vip_card\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            GoodsEditHandler::class,
            GoodsDeleteHandler::class,
            OrderCreatedHandler::class,
        ];
    }
}
