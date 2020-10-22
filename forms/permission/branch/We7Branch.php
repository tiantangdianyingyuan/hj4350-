<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 16:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\branch;


use app\forms\common\CommonAuth;
use app\forms\common\CommonOption;
use app\models\Option;

class We7Branch extends BaseBranch
{
    public $ignore = 'we7';
    /**
     * @param $menu
     * @return mixed
     * @throws \Exception
     * 删除非本分支菜单
     */
    public function deleteMenu($menu)
    {
        if (isset($menu['ignore']) && in_array($this->ignore, $menu['ignore'])) {
            return true;
        }
        return false;
    }

    public function logoutUrl()
    {
        return \Yii::$app->urlManager->createUrl('mall/we7-entry/logout');
    }

    public function childPermission($adminInfo)
    {
        if ($adminInfo->is_default == 1) {
            $status = CommonOption::get(
                Option::NAME_PERMISSIONS_STATUS,
                0,
                Option::GROUP_ADMIN
            );
            if ($status && $status == 1) {
                $default = CommonAuth::getPermissionsList();
                $permission = $this->getKey($default);
            } else {
                $permission = [];
            }
        } else {
            $permission = parent::childPermission($adminInfo);
        }
        return $permission;
    }

    public function getSecondaryPermission($adminInfo)
    {
        if ($adminInfo->is_default == 1) {
            $status = CommonOption::get(
                Option::NAME_PERMISSIONS_STATUS,
                0,
                Option::GROUP_ADMIN
            );
            if ($status && $status == 1) {
                $permission = CommonAuth::getSecondaryPermissionAll();
            } else {
                $permission = CommonAuth::getSecondaryPermission();
            }
        } else {
            $permission = parent::getSecondaryPermission($adminInfo);
        }
        return $permission;
    }
}
