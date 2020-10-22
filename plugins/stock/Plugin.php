<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/3
 * Time: 14:12
 */

namespace app\plugins\stock;

use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\stock\forms\common\CommonStock;
use app\plugins\stock\forms\common\StockReview;
use app\plugins\stock\forms\mall\StatisticsForm;
use app\plugins\stock\forms\mall\SettingForm;
use app\plugins\stock\forms\mall\StockForm;
use app\plugins\stock\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '股东分红设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/stock/mall/setting/index',
            ],
            [
                'name' => '股东管理',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/stock/mall/stock/index'
            ],
            [
                'name' => '股东等级',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/stock/mall/level/index',
            ],
            [
                'name' => '分红结算',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/stock/mall/balance/index',
                'action' => [
                    [
                        'name' => '分红结算',
                        'route' => 'plugin/stock/mall/balance/add',
                    ],
                    [
                        'name' => '分红结算',
                        'route' => 'plugin/stock/mall/balance/detail',
                    ],
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
        return 'stock';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '股东分红';
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
            'route' => 'mall/stock-statistics/index',
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/stock/mall/setting/index';
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

    public function getStockForm()
    {
        $form = new SettingForm();
        return $form;
    }

    public function getSmsSetting()
    {
        return [
            'stock' => [
                'title' => '分销商成为股东提醒',
                'content' => '例如：模板内容：您已成为股东，可参与股东分红',
                'support_mch' => false,
                'loading' => false,
                'variable' => []
            ],
            'stock_level_up' => [
                'title' => '股东升级提醒',
                'content' => '例如：模板内容：您已升级为${name},分红比例更改为${number}',
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

    public function getStockReview()
    {
        return new StockForm();
    }

    public function getStockApply()
    {
        return new CommonStock();
    }

    public function getCommonStock()
    {
        return new CommonStock();
    }

    public function getCashConfig()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'class' => 'app\\plugins\\stock\\models\\StockCash',
            'user_class' => 'app\\plugins\\stock\\models\\StockUserInfo',
            'user_alias' => 'stock_user'
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
        return '股东';
    }

    public function getReviewClass($config = [])
    {
        return new StockReview($config);
    }
}
