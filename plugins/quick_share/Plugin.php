<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\quick_share;

use app\forms\PickLinkForm;
use app\helpers\PluginHelper;

class Plugin extends \app\plugins\Plugin
{

    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/quick_share/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '发圈素材管理',
                'route' => 'plugin/quick_share/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '添加发圈素材',
                        'route' => 'plugin/quick_share/mall/goods/edit',
                    ]
                ]
            ],
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'quick_share';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '一键发圈';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'quick_share_head' => $imageBaseUrl . '/quick_share-head.png',
            ]
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/quick_share/mall/goods/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'quick_share',
                'name' => '一键发圈',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-quick-share.png',
                'value' => '/plugins/quick_share/index/index',
                'ignore' => [],
            ],
        ];
    }

    public function getBlackList()
    {
        return [];
    }
}
