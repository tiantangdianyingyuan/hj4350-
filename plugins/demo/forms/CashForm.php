<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/30
 * Time: 17:27
 */

namespace app\plugins\demo\forms;

use app\forms\mall\finance\BaseCashApply;

class CashForm extends BaseCashApply
{
    protected function beforeApply($cash)
    {
        return true;
    }

    protected function afterApply($cash)
    {
        return true;
    }

    protected function beforeRemit($cash)
    {
        return true;
    }

    protected function afterRemit($cash)
    {
        \Yii::error('提现了1000元');
        return true;
    }

    protected function beforeReject($cash)
    {
        return true;
    }

    protected function afterReject($cash)
    {
        return true;
    }
}
