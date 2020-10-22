<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common;


use app\core\Pagination;
use app\forms\common\attachment\CommonAttachment;
use app\forms\Menus;
use app\models\AdminInfo;
use app\models\User;
use app\models\UserIdentity;
use app\models\We7App;
use yii\db\Query;

class CommonAuth
{
    private $superAdminPermissionRoutes = [];
    private $adminPermissionRoutes = [];
    private $notPermissionRoutes = [];

    /**
     * TODO 用于微擎版
     * @param $search
     * @return array
     */
    public static function getChildrenUsers($search)
    {
        $query = new Query();
        $query->select('u.uid,u.username as ins_username,u.joindate,ai.*,uu.*')->from(['u' => we7_table_name('users')])
            ->leftJoin(['ai' => AdminInfo::tableName()], 'u.uid=ai.we7_user_id')
            ->leftJoin(['uu' => User::tableName()], 'ai.user_id=uu.id');
        if ($search['keyword']) {
            $query->andWhere([
                'OR',
                ['LIKE', 'u.uid', $search['keyword']],
                ['LIKE', 'u.username', $search['keyword']],
            ]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('u.uid DESC')->all();

        $newList = [];
        foreach ($list as $item) {
            $arr = $item;
            $arr['adminInfo']['permissions'] = $item['permissions'];
            $arr['adminInfo']['is_default'] = $item['is_default'] ?: 0;
            $arr['username'] = $item['ins_username'];
            $arr['created_at'] = date('Y-m-d H:i:s', $item['joindate']);
            $arr['identity'] = [];
            $arr['id'] = $item['uid'];
            $arr['secondary_permissions'] = self::getSecondaryPermissionList($item['secondary_permissions']);
            $newList[] = $arr;
        }

        return [
            'list' => $newList,
            'pagination' => $pagination
        ];
    }

    /**
     * 获取子账号总数
     * @return int
     */
    public static function getChildrenNum()
    {
        $query1 = UserIdentity::find()->alias('i')->where(['i.is_admin' => 1])->select('i.user_id');
        $list = User::find()->alias('u')->where([
            'u.mall_id' => \Yii::$app->user->identity->mall_id,
            'u.is_delete' => 0,
        ])
            ->andWhere(['u.id' => $query1])
            ->select('u.id')->asArray()->all();

        return count($list);
    }

    /**
     * 获取总管理员可分配的权限列表
     */
    public static function getPermissionsList()
    {
        return [
            'mall' => [
                [
                    'display_name' => '优惠券',
                    'name' => 'coupon',
                ],
                [
                    'display_name' => '分销',
                    'name' => 'share',
                ],
                [
                    'display_name' => '专题',
                    'name' => 'topic',
                ],
                [
                    'display_name' => '视频',
                    'name' => 'video',
                ],
                [
                    'display_name' => '版权设置',
                    'name' => 'copyright',
                ],
                [
                    'display_name' => '员工管理',
                    'name' => 'rule_user',
                ],
                [
                    'display_name' => '上传设置',
                    'name' => 'attachment',
                ],
                [
                    'display_name' => '小程序直播',
                    'name' => 'live',
                ],
                [
                    'display_name' => '满减设置',
                    'name' => 'full-reduce',
                ],
            ],
            'plugins' => \Yii::$app->plugin->list
        ];
    }

    /**
     * 获取子账号管理员不能访问的路由
     */
    public static function getPermissionsRouteList()
    {
        // TODO 此处要使用缓存
        $adminMenus = Menus::getAdminMenus();
        $mallMenus = Menus::getMallMenus();
        $menus = array_merge($adminMenus, $mallMenus);

        $commonAuth = new CommonAuth();

        $adminPermissionKeys = \Yii::$app->role->permission;
        $superAdminPermissionKeys = Menus::MALL_SUPER_ADMIN_KEY;

        $commonAuth->getMenusRoute($menus, $adminPermissionKeys, $superAdminPermissionKeys);

        return $commonAuth->notPermissionRoutes;
    }

    private function getMenusRoute($menus, $adminKeys, $superAdminKeys)
    {
        foreach ($menus as $k => $item) {
            if (isset($item['key']) && !in_array($item['key'], $adminKeys)) {
                $this->notPermissionRoutes[] = $item['route'];
            }

            if (isset($item['key']) && in_array($item['key'], $superAdminKeys)) {
                $this->notPermissionRoutes[] = $item['route'];
            }

            if (isset($item['children'])) {
                $menus[$k]['children'] = $this->getMenusRoute($item['children'], $adminKeys, $superAdminKeys);
            }
        }

        return $menus;
    }

    public static function getAllPermission()
    {
        $permissions = self::getPermissionsList();
        $list = [];
        foreach ($permissions as $key => $permission) {
            if (is_array($permission)) {
                foreach ($permission as $value) {
                    if (isset($value['name'])) {
                        if ($key == 'plugins') {
                            try {
                                $plugin = \Yii::$app->plugin->getPlugin($value['name']);
                            } catch (\Exception $exception) {
                                continue;
                            }
                        }
                        $list[] = $value['name'];
                    }
                }
            }
        }
        return $list;
    }

    /**
     * @return array
     * 二级菜单的所有权限
     */
    public static function getSecondaryPermissionAll()
    {
        $secondaryPermissions = [
            'attachment' => CommonAttachment::getCommon()->getDefaultAuth(),
            'template' => [
                'is_all' => 1,
                'list' => [],
                'use_all' => '1',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @return array
     * 二级菜单的没有权限
     */
    public static function getSecondaryPermission()
    {
        $secondaryPermissions = [
            'attachment' => [],
            'template' => [
                'is_all' => 0,
                'list' => [],
                'use_all' => '0',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @return array
     * 二级权限的默认权限
     */
    public static function secondaryDefault()
    {
        $secondaryPermissions = [
            'attachment' => CommonAttachment::getCommon()->getDefaultAuth(),
            'template' => [
                'is_all' => '0',
                'list' => [],
                'use_all' => '0',
                'use_list' => [],
            ]
        ];
        return $secondaryPermissions;
    }

    /**
     * @param $json
     * @return array
     * 兼容新的二级权限
     */
    public static function getSecondaryPermissionList($json)
    {
        $secondaryDefault = CommonAuth::secondaryDefault();
        if ($json) {
            $secondaryPermissions = json_decode($json, true);
            foreach ($secondaryDefault as $key => $item) {
                if (!isset($secondaryPermissions[$key])) {
                    $secondaryPermissions[$key] = $item;
                    continue;
                }
                switch ($key) {
                    case 'template':
                        foreach ($item as $index => $value) {
                            if (!isset($secondaryPermissions[$key][$index])) {
                                $secondaryPermissions[$key][$index] = $value;
                            }
                        }
                        $list = [];
                        $userList = [];
                        try {
                            $plugin = \Yii::$app->plugin->getPlugin('diy');
                            if (count($secondaryPermissions[$key]['list']) > 0) {
                                $list = $plugin->getMarketListById(array_column($secondaryPermissions[$key]['list'], 'id'));
                            }
                            if (count($secondaryPermissions[$key]['use_list']) > 0) {
                                $userList = $plugin->getLocalListById(array_column($secondaryPermissions[$key]['use_list'], 'id'));
                            }
                        } catch (\Exception $exception) {
                        }
                        $secondaryPermissions[$key]['list'] = $list;
                        $secondaryPermissions[$key]['use_list'] = $userList;
                        break;
                }
            }
        } else {
            $secondaryPermissions = $secondaryDefault;
        }
        return $secondaryPermissions;
    }
}
