<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\scratch;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\scratch\forms\common\CommonScratch;
use app\plugins\scratch\handlers\HandlerRegister;
use app\handlers\HandlerBase;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/scratch/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '奖品列表',
                'route' => 'plugin/scratch/mall/scratch/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '奖品编辑',
                        'route' => 'plugin/scratch/mall/scratch/edit',
                    ],
                ]
            ],
            [
                'name' => '抽奖记录',
                'route' => 'plugin/scratch/mall/log/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '赠品订单',
                'route' => 'plugin/scratch/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/scratch/mall/order/detail',
                    ],
                ]
            ],
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
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'scratch';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '刮刮卡';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'scratch_bg' => $imageBaseUrl . '/scratch-bg.png',
                'scratch_win' => $imageBaseUrl . '/scratch-win.png'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/scratch/mall/scratch/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'scratch',
                'name' => '刮刮卡',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-scratch.png',
                'value' => '/plugins/scratch/index/index',
                'ignore' => [],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = CommonScratch::getSetting();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => 0,
            'support_share' => 1,
        ]);
        return $config;
    }

    public function getBlackList()
    {
        return [
            'plugin/scratch/api/scratch/order-submit',
        ];
    }

    public function supportEcard()
    {
        return true;
    }
}
