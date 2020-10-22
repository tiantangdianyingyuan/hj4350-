<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role;


use app\forms\common\CommonOption;
use app\forms\common\CommonUser;
use app\forms\Menus;
use app\forms\permission\menu\MenusForm;
use app\models\AdminInfo;
use app\models\Model;
use app\models\Option;
use app\plugins\Plugin;

class RolePermissionForm extends Model
{
    private $userIdentity;

    /**
     * 角色添加/编辑时 所能分配的权限
     * @return mixed
     */
    public function getList()
    {
        $form = new MenusForm();
        $form->isExist = true;
        $res = $form->getMenus('mall');
        // 教程管理要追加进去
        $res['menus'][] = $res['courseMenu'];

        $this->userIdentity = CommonUser::getUserIdentity();
        $newMenuList = $this->deleteAdminMenu($res['menus']);

        // 教程管理权限
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = CommonOption::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);
        // 获取插件中心路由
        $adminPermissions = [];
        $identity = \Yii::$app->user->identity->identity;
        if ($identity->is_admin == 1) {
            $adminInfo = AdminInfo::findOne(['user_id' => \Yii::$app->user->id]);
            if ($adminInfo) {
                $adminPermissions = json_decode($adminInfo->permissions, true);
            }
        }
        foreach ($newMenuList as $key => &$item) {
            $item = $this->setPluginData($item, $identity, $adminPermissions);
            if ($setting['status'] == 0) {
                if (isset($item['key']) && $item['key'] == 'course') {
                    unset($newMenuList[$key]);
                }
            }
            if (isset($item['key']) && $item['key'] == 'app-manage') {
                unset($newMenuList[$key]);
            }
        }
        unset($item);

        return array_values($newMenuList);
    }

    /**
     * 去除总管理员独有的菜单，这些菜单子账号和操作员都不能使用
     * @param $list
     * @return mixed
     */
    public function deleteAdminMenu($list)
    {
        foreach ($list as $k => $item) {
            $removePermissions = array_merge(['rule_user'], Menus::MALL_SUPER_ADMIN_KEY);

            if (isset($item['key']) && in_array($item['key'], $removePermissions)) {
                unset($list[$k]);
                continue;
            }

            // 员工不需要这个菜单
            if (isset($item['route']) && $item['route'] == 'admin/setting/attachment') {
                unset($list[$k]);
            }

            if (isset($item['children']) && count($item['children']) > 0) {
                $list[$k]['children'] = $this->deleteAdminMenu($item['children']);
            }
        }
        $list = array_values($list);
        return $list;
    }

    private function setPluginData($item, $identity, $adminPermissions)
    {
        if (isset($item['key']) && $item['key'] == 'plugins') {
            $pluginData = $this->setData($identity, $adminPermissions);
            $item['children'] = $pluginData;
        }
        if (isset($item['children'])) {
            foreach ($item['children'] as $key => $child) {
                $item['children'][$key] = $this->setPluginData($child, $identity, $adminPermissions);
            }
        }
        return $item;
    }

    private function setData($identity, $adminPermissions)
    {
        $plugins = \Yii::$app->role->getMallRole();
        $pluginMenus = [];
        foreach ($plugins->permission as $plugin) {
            // 子账号需判断是否
            if ($identity->is_super_admin != 1 && !in_array($plugin, $adminPermissions)) {
                continue;
            }
            $PluginClass = 'app\\plugins\\' . $plugin . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getMenus')) {
                $menus = $object->getMenus();
                if ($menus) {
                    $newMenus = [
                        'name' => $object->getDisplayName(),
                        'icon' => '',
                        'children' => $menus,
                        'route' => $menus[0]['route'],
                    ];
                    $pluginMenus[] = $newMenus;
                }
            }
        }
        return $pluginMenus;
    }
}
