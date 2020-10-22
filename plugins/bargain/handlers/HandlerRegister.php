<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\plugins\bargain\handlers;


use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    const BARGAIN_TIMER = 'bargain_timer';
    const BARGAIN_USER_JOIN = 'bargain_user_join';
    public function getHandlers()
    {
        return [
            GoodsTimeHandler::class,
            BargainUserJoinHandler::class,
            OrderCreatedHandler::class,
            OrderCancelHandler::class,
        ];
    }
}
