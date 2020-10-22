<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/6/28
 * Time: 11:55
 */

namespace app\plugins\app_admin;

use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\handlers\HandlerBase;

class Plugin extends \app\plugins\Plugin
{

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'app_admin';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '手机端管理';
    }

    public function getIndexRoute()
    {
        return 'plugin/app_admin/mall/index/index';
    }

    public function getMenus()
    {
        return [
            [
                'name' => '管理员列表',
                'route' => 'plugin/app_admin/mall/index/index',
                'icon' => 'el-icon-setting',
            ]
        ];
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'app_admin',
                'type' => 'app_admin',
                'name' => '商城管理',
                'open_type' => 'app_admin',
                'icon' => $iconBaseUrl . '/icon-setting.png',
                'value' => '/pages/app_admin/index/index',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE]
            ],
        ];
    }
}
