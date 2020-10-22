<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 15:54
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common\continue_type;


class UnlimitedState extends BaseState
{
    public function setJob()
    {
    }

    public function clearContinue()
    {
        return 0;
    }
}
