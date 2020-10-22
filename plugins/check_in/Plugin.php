<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */


namespace app\plugins\check_in;


use app\helpers\PluginHelper;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\common\CommonTemplate;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'check_in';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '签到插件';
    }


    /**
     * 获取插件菜单列表
     * @return array
     */
    public function getMenus()
    {
        return [
            [
                'name' => '签到设置',
                'route' => 'plugin/check_in/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/check_in/mall/index/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '自定义设置',
                'route' => 'plugin/check_in/mall/index/customize',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '签到记录',
                'route' => 'plugin/check_in/mall/index/log'
            ],
        ];
    }

    /**
     * 插件的小程序端配置，小程序端可使用getApp().config(e => { e.plugin.xxx });获取配置，xxx为插件唯一id
     * @return array
     */
    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'check_in' => $imageBaseUrl . '/check-in.png',
                'get' => $imageBaseUrl . '/get.png',
                'getRed' => $imageBaseUrl . '/getRed.png',
                'over' => $imageBaseUrl . '/over.png',
                'success' => $imageBaseUrl . '/success.png',
                'top_bg' => $imageBaseUrl . '/top-bg.png',
            ]
        ];
    }

    /**
     * 获取插件入口路由
     * @return string|null
     */
    public function getIndexRoute()
    {
        return 'plugin/check_in/mall/index/index';
    }

    /**
     * 插件可共用的跳转链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'check_in',
                'name' => '签到',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-check-in.png',
                'value' => '/plugins/check_in/index/index',
            ],
        ];
    }

    public function getUserInfo($user)
    {
        $common = Common::getCommon(\Yii::$app->mall);
        $checkInUser = $common->getCheckInUser($user);
        $todayAward = $common->getAwardConfigNormal();
        return [
            'check_in' => [
                'continue' => $checkInUser->continue,
                'total' => $checkInUser->total,
                'todayAward' => $todayAward ? $todayAward->getExplain() : ''
            ]
        ];
    }

    public function templateList()
    {
        return [
            'check_in_tpl' => CommonTemplate::class,
        ];
    }
}
