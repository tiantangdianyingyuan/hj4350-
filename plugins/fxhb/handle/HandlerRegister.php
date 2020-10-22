<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:32
 */


namespace app\plugins\fxhb\handle;


use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    const FXHB_JOIN_ACTIVITY = 'fxhb_join_activity';
    public function getHandlers()
    {
        return [
            JoinActivityHandle::class
        ];
    }
}
