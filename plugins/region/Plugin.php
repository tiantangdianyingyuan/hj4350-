<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 14:12
 */

namespace app\plugins\region;

use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\forms\common\RegionReview;
use app\plugins\region\forms\mall\RegionEditForm;
use app\plugins\region\forms\mall\RegionForm;
use app\plugins\region\forms\mall\SettingForm;
use app\plugins\region\forms\mall\StatisticsForm;
use app\plugins\region\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '区域代理设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/region/mall/setting/index',
            ],
            [
                'name' => '代理管理',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/region/mall/region/index'
            ],
            [
                'name' => '代理级别',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/region/mall/level/index',
            ],
            [
                'name' => '分红结算',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/region/mall/balance/index',
                'action' => [
                    [
                        'name' => '分红结算',
                        'route' => 'plugin/region/mall/balance/add',
                    ],
                    [
                        'name' => '结算详情',
                        'route' => 'plugin/region/mall/balance/detail'
                    ]
                ]
            ],
            [
                'name' => '分红订单',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/region/mall/order/index',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/region/mall/order/detail',
                    ]
                ]
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'region';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '区域代理';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg'
            ],
        ];
    }

    /**
     * 返回实例化后台统计数据接口
     * @return StatisticsForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/region-statistics/index',
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/region/mall/setting/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        return [
        ];
    }

    public function getRegionForm()
    {
        $form = new SettingForm();
        return $form;
    }

    public function getSmsSetting()
    {
        return [
            'region' => [
                'title' => '分销商成为代理提醒',
                'content' => '例如：模板内容：您已成为${name}区域代理，可参与区域代理分红',
                'tip' => '用于区域代理',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容：您已成为${name}区域代理，可参与区域代理分红，则需填写name'
                    ],
                ]
            ],
            'region_level_up' => [
                'title' => '代理升级提醒',
                'content' => '例如：模板内容：您已升级为${name},分红比例更改为${number}',
                'tip' => '用于区域代理',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容：您已升级为${name},分红比例更改为${number}，则需填写name'
                    ],
                    [
                        'key' => 'number',
                        'value' => '模板变量number',
                        'desc' => '例如：模板内容：您已升级为${name},分红比例更改为${number}，则需填写number'
                    ],
                ]
            ],
        ];
    }

    public function getRegionReview()
    {
        return new RegionForm();
    }

    public function getRegionApply()
    {
        return new RegionEditForm();
    }

    public function getCommonRegion()
    {
        return new Commonregion();
    }

    public function getCashConfig()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'class' => 'app\\plugins\\region\\models\\RegionCash',
            'user_class' => 'app\\plugins\\region\\models\\RegionUserInfo',
            'user_alias' => 'region_user'
        ];
    }

    public function needCheck()
    {
        return true;
    }

    public function needCash()
    {
        return true;
    }

    public function identityName()
    {
        return '代理';
    }

    public function getReviewClass($config = [])
    {
        return new RegionReview($config);
    }
}
