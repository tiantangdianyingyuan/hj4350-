<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 14:26
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\role;


use app\forms\common\CommonOption;
use app\forms\common\CommonUser;
use app\models\Option;
use app\models\User;
use app\plugins\Plugin;

class OperatorRole extends BaseRole
{
    private $creatorPluginPermissions = false;

    public function getName()
    {
        return 'operator';
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
        $userPermissions = CommonUser::getUserPermissions();
        // 教程管理权限
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = CommonOption::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);
        if ($setting['status'] == 0) {
            foreach ($userPermissions as $key => $userPermission) {
                if ($userPermission == 'mall/tutorial/index') {
                    unset($userPermissions[$key]);
                }
                if ($userPermission == 'admin/setting/attachment') {
                    unset($userPermissions[$key]);
                }
            }
            $userPermissions = array_values($userPermissions);
        }
        $this->permission = $userPermissions;
    }

    public function getCreatorPluginPermissions()
    {
        if ($this->creatorPluginPermissions !== false)
            return $this->creatorPluginPermissions;
        if (!empty(\Yii::$app->user->identity->role)) {
            $roles = \Yii::$app->user->identity->role;
            $creatorUser = User::find()
                ->where([
                    'id' => $roles[0]['creator_id'],
                ])->one();
            $creatorRole = new AdminRole([
                'userIdentity' => $creatorUser->identity,
                'user' => $creatorUser,
                'mall' => null,
            ]);
            $this->creatorPluginPermissions = $creatorRole->getPluginPermission();
        } else {
            $this->creatorPluginPermissions = null;
        }
        return $this->creatorPluginPermissions;
    }

    public function checkPlugin($plugin)
    {
        if (!($plugin instanceof Plugin)) {
            return false;
        }
        $permission = $this->getPermission();
        $flag = false;
        foreach ($plugin->getMenus() as $menu) {
            if (isset($menu['route']) && in_array($menu['route'], $permission)) {
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            return false;
        }
        $creatorPluginPermissions = $this->getCreatorPluginPermissions();
        if (is_array($creatorPluginPermissions)) {
             if (!in_array($plugin->getName(), $creatorPluginPermissions)) return false;
        }
        return true;
    }

    public function getAccountPermission()
    {
        /* @var User $user */
        $user = User::find()->with(['identity'])
            ->where(['id' => $this->mall->user_id])
            ->one();
        $config = [
            'userIdentity' => $user->identity,
            'user' => $user,
            'mall' => $this->mall
        ];
        if ($user->identity->is_super_admin == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->identity->is_admin == 1) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent->permission;
    }

    /**
     * @param Plugin $plugin
     * @return mixed
     */
    protected function getPluginIndexRoute($plugin)
    {
        $default = $plugin->getIndexRoute();
        if (in_array($default, $this->permission)) {
            return $default;
        } else {
            foreach ($plugin->getMenus() as $item) {
                if (isset($item['is_jump']) && $item['is_jump'] == 0) {
                    return $default;
                }
                if (in_array($item['route'], $this->permission)) {
                    return $item['route'];
                }
            }
            return $default;
        }
    }

    public function getAccount()
    {
        /* @var User $user */
        $user = User::find()->with(['identity'])
            ->where(['id' => $this->mall->user_id])
            ->one();
        $config = [
            'userIdentity' => $user->identity,
            'user' => $user,
            'mall' => $this->mall
        ];
        if ($user->identity->is_super_admin == 1) {
            $parent = new SuperAdminRole($config);
        } elseif ($user->identity->is_admin == 1) {
            $parent = new AdminRole($config);
        } else {
            throw new \Exception('错误的账户');
        }
        return $parent;
    }

    public function getTemplate()
    {
        $parent = $this->getAccount();
        return $parent->getTemplate();
    }
}
