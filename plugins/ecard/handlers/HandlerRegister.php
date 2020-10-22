<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/3/17
 * Time: 11:33
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\handlers;


use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderCanceledHandle::class
        ];
    }
}
