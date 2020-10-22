<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 14:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\role;


use app\forms\common\CommonUser;

class MchRole extends BaseRole
{
    public function getName()
    {
        return 'mch';
    }

    public function deleteRoleMenu($menu)
    {
        if (isset($menu['route']) && !in_array($menu['route'], $this->getPermission())) {
            return true;
        }
        return false;
    }

    public function setPermission()
    {
        $this->permission = CommonUser::getMchPermissions();
    }

    public function getAccountPermission()
    {
        return false;
    }

    public function getAccount()
    {
        return false;
    }
}
