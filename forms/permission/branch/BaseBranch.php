<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 10:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\branch;


use app\forms\common\CommonAuth;
use app\models\AdminInfo;
use app\models\Model;

abstract class BaseBranch extends Model
{
    public $ignore;

    /**
     * @param $menu
     * @return mixed
     * @throws \Exception
     * 删除非本分支菜单
     */
    abstract public function deleteMenu($menu);

    /**
     * @return mixed
     * 获取商城退出跳转链接
     */
    abstract public function logoutUrl();

    /**
     * @param AdminInfo $adminInfo
     * @return array
     * 获取子账户权限
     */
    public function childPermission($adminInfo)
    {
        $all = CommonAuth::getAllPermission();
        if ($adminInfo->identity->is_super_admin == 1) {
            return $all;
        }
        $permission = [];
        if ($adminInfo->permissions) {
            $permission = json_decode($adminInfo->permissions, true);
        }
        $permission = array_intersect($permission, $all);
        return $permission;
    }

    protected function getKey($list)
    {
        $newList = [];
        foreach ($list as $item) {
            if (isset($item['name'])) {
                $newList[] = $item['name'];
            } elseif (is_array($item)) {
                $newList = array_merge($newList, $this->getKey($item));
            } else {
                continue;
            }
        }
        return $newList;
    }

    /**
     * @param AdminInfo $adminInfo
     * @return array|mixed
     */
    public function getSecondaryPermission($adminInfo)
    {
        if ($adminInfo->identity->is_super_admin == 1) {
            return CommonAuth::getSecondaryPermissionAll();
        }
        $permission = [];
        if ($adminInfo->secondary_permissions) {
            $permission = json_decode($adminInfo->secondary_permissions, true);
        }
        return $permission;
    }
}
