<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/2/28
 * Time: 9:14
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\handlers;


use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            GoodsDestroyHandler::class,
            OrderPayedHandler::class,
            OrderCanceledHandler::class,
        ];
    }
}
