<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 11:57
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\menu;


use app\forms\mall\plugin\PluginListForm;
use app\forms\Menus;
use app\forms\permission\branch\BaseBranch;
use app\forms\permission\role\BaseRole;
use app\models\Model;

/**
 * @property BaseBranch $branch
 * @property BaseRole $role
 */
class MenusForm extends Model
{
    private $branch;
    private $role;

    public $currentRouteInfo = [];
    public $currentRoute;
    public $type;
    public $isExist = false;
    public $pluginObject;

    /**
     * 有实际页面且不菜单列表中的路由填写在此处
     */
    const existList = [
        'mall/index/index',
        'admin/cache/clean',
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->branch = \Yii::$app->branch;
        $this->role = \Yii::$app->role;
    }

    /**
     * @param $type string 有效参数mall|plugin
     * @return array
     * @throws \Exception
     * 获取菜单
     */
    public function getMenus($type)
    {
        if (!in_array($type, ['admin', 'mall', 'plugin'])) {
            throw new \Exception('type 传入参数无效');
        }

        switch ($type) {
            case 'admin':
                $originalMenus = Menus::getAdminMenus();
                break;
            case 'mall':
                $originalMenus = Menus::getMallMenus();
                break;
            case 'plugin':
                $plugin = $this->pluginObject ?: \Yii::$app->plugin->currentPlugin;
                $originalMenus = $plugin->getMenus();
                break;
            default:
                throw new \Exception('type 传入参数无效');
        }
        // 去除不需显示的菜单
        $menus = $this->deleteMenu($originalMenus);
        // 菜单列表
        $menus = $this->resetMenus($menus);

        if (!$this->isExist) {
            if (!in_array($this->currentRoute, self::existList)) {
                // 开启调试模式才显示
                if (env('YII_DEBUG')) {
                    throw new \Exception('页面路由未正常配置（会导致员工账号无法进入该页面）,请检查');
                }
            }
        }
        $courseMenu = [];
        foreach ($menus as $key => $menu) {
            // 教程管理菜单 移到顶部显示
            if (isset($menu['key']) && $menu['key'] == 'course') {
                $courseMenu = $menu;
                unset($menus[$key]);
                break;
            }

        }
        $menus = array_values($menus);
        $this->setPluginCats($menus);
        return [
            'menus' => $menus,
            'currentRouteInfo' => $this->currentRouteInfo,
            'courseMenu' => $courseMenu,
        ];
    }

    /**
     * @param $menus
     * @return array
     * @throws \Exception
     * 去除非本分支和本角色拥有的菜单
     */
    public function deleteMenu($menus)
    {
        foreach ($menus as $index => $item) {
            //插件统计左侧菜单隐藏
            if (isset($item['is_statistics_show']) && $item['is_statistics_show'] == false) {
                unset($menus[$index]);
                continue;
            }
            $menus[$index]['is_show'] = true;
            if ($this->branch->deleteMenu($item)) {
                unset($menus[$index]);
                continue;
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->deleteMenu($item['children']);
                if (count($item['children']) <= 0) {
                    unset($menus[$index]);
                    continue;
                } else {
                    $item['route'] = $item['children'][0]['route'];
                    $menus[$index]['route'] = $item['children'][0]['route'];
                    $menus[$index]['children'] = $item['children'];
                }
            }
            if ($this->role->deleteMenu($item)) {
                unset($menus[$index]);
                continue;
            }
        }
        $menus = array_values($menus);
        return $menus;
    }

    /**
     * 给自定义路由列表 追加ID 及 PID
     * @param array $list 自定义的多维路由数组
     * @param int $id 权限ID
     * @param int $pid 权限PID
     * @return mixed
     */
    private function resetMenus(array $list, &$id = 1, $pid = 0)
    {
        foreach ($list as $key => $item) {
            $list[$key]['id'] = (string)$id;
            $list[$key]['pid'] = (string)$pid;

            // 前端选中的菜单
            if (isset($list[$key]['route']) && $this->currentRoute === $list[$key]['route']) {
                $this->currentRouteInfo = $list[$key];
                $list[$key]['is_active'] = true;
                $this->isExist = true;
            }
            if (isset($list[$key]['action'])) {
                foreach ($list[$key]['action'] as $aItem) {
                    if (isset($aItem['route']) && $aItem['route'] === $this->currentRoute) {
                        $list[$key]['is_active'] = true;
                        $this->isExist = true;
                    }
                }
            }

            if (isset($item['children'])) {
                $id++;
                $list[$key]['children'] = $this->resetMenus($item['children'], $id, $id - 1);
                foreach ($list[$key]['children'] as $cKey => $child) {
                    if (isset($child['is_active']) && $child['is_active'] == true) {
                        $list[$key]['is_active'] = true;
                    }
                }
            }

            if (isset($item['action'])) {
                $id++;
                $list[$key]['action'] = $this->resetMenus($item['action'], $id, $id - 1);
            }

            isset($item['children']) == false && isset($item['action']) == false ? $id++ : $id;
        }

        return $list;
    }

    private function setPluginCats(&$menus)
    {
        if (\Yii::$app->requestedRoute !== 'mall/menus/index') return;
        foreach ($menus as &$menu) {
            if (!isset($menu['children']) || !is_array($menu['children'])) continue;
            foreach ($menu['children'] as $index => &$item) {
                if (isset($item['key']) && $item['key'] === 'plugins') {
                    $urlParams = json_decode(\Yii::$app->request->post('url_params', '{}'), true);
                    $currentCat = null;
                    if (!empty($urlParams['cat_name'])) {
                        $currentCat = urldecode($urlParams['cat_name']);
                    }
                    if ($currentCat && $item['is_active']) $item['is_active'] = false;
                    $form = new PluginListForm();
                    $data = $form->search();
                    if (!isset($data['data']['cats']) || !is_array($data['data']['cats'])) break;
                    $insertPosition = $index + 1;
                    foreach ($data['data']['cats'] as $cat) {
                        if ($cat['name'] === 'other') continue;
                        array_insert($menu['children'], $insertPosition, [
                            'id' => 'cat_id_' . $cat['name'],
                            'name' => $cat['display_name'],
                            'route' => 'mall/plugin/index',
                            'params' => [
                                'cat_name' => $cat['name'],
                            ],
                            'is_active' => $currentCat === $cat['name'],
                        ]);
                        $insertPosition++;
                    }
                    break;
                };
            }
        }
    }
}
