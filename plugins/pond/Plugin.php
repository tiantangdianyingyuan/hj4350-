<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\pond;

use app\controllers\Controller;
use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\pond\forms\common\CommonPond;
use app\plugins\pond\handlers\HandlerRegister;
use app\handlers\HandlerBase;
use yii\base\Event;

class Plugin extends \app\plugins\Plugin
{

    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/pond/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '奖品列表',
                'route' => 'plugin/pond/mall/pond/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '抽奖记录',
                'route' => 'plugin/pond/mall/log/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '赠品订单',
                'route' => 'plugin/pond/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/pond/mall/order/detail',
                    ],
                    [
                        'name' => '订单列表',
                        'route' => 'plugin/pond/mall/order',
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
        return 'pond';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '九宫格';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'pond_head' => $imageBaseUrl . '/pond-head.png',
                'pond_success' => $imageBaseUrl . '/pond-success.png',
                'pond_empty' => $imageBaseUrl . '/pond-empty.png',
            ]
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/pond/mall/pond/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'pond',
                'name' => '九宫格抽奖',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pond.png',
                'value' => '/plugins/pond/index/index',
                'ignore' => [],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = CommonPond::getSetting();
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
            'plugin/pond/api/pond/order-submit',
            'plugin/pond/api/pond/lottery',
        ];
    }

    public function supportEcard()
    {
        return true;
    }
}
