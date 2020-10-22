<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/6/30
 * Time: 17:08
 */

namespace app\plugins\demo\forms;

use app\forms\api\finance\BaseFinanceCashForm;
use app\forms\api\finance\UserInfo;

class ApiCashForm extends BaseFinanceCashForm
{
    protected function beforeCashValidate()
    {
        return true;
    }

    protected function afterSave()
    {
        return true;
    }

    protected function setUserInfo(UserInfo $userInfo)
    {
        $userInfo->name = '张三';
        $userInfo->phone = '13131313131';
        return $userInfo;
    }

    protected function setModel()
    {
        return 'demo';
    }
}
