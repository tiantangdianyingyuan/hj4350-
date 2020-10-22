<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/3
 * Time: 14:12
 */

namespace app\plugins\bonus;

use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\bonus\forms\common\BonusReview;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\forms\mall\CaptainForm;
use app\plugins\bonus\forms\mall\OrderBonusForm;
use app\plugins\bonus\forms\mall\SettingForm;
use app\plugins\bonus\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '队长管理',
                'route' => 'plugin/bonus/mall/captain/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/bonus/mall/captain/detail',
                    ]
                ]
            ],
            [
                'name' => '队长等级设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/bonus/mall/members/index',
                'action' => [
                    [
                        'name' => '新增/编辑等级',
                        'route' => 'plugin/bonus/mall/members/edit',
                    ]
                ]
            ],
            [
                'name' => '分红订单',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/bonus/mall/order/index',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/bonus/mall/order/detail',
                    ]
                ]
            ],
            [
                'name' => '设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/bonus/mall/setting/index',
            ],
            // [
            //     'name' => '消息通知',
            //     'icon' => 'el-icon-star-on',
            //     'route' => 'plugin/bonus/mall/setting/template',
            // ],
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
        return 'bonus';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '团队分红';
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

    public function getIndexRoute()
    {
        return 'plugin/bonus/mall/captain/index';
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

    public function getSmsSetting()
    {
        return [
            'bonus' => [
                'title' => '分销商成为队长提醒',
                'content' => '例如：模板内容：您有一名下线分销商${name}成为队长，请登录商城后台查看',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容: "您有一名下线分销商${name}成为队长，请登录商城后台查看"，则需填写name'
                    ],
                ]
            ],
        ];
    }

    public function getBonusForm()
    {
        $form = new SettingForm();
        return $form;
    }

    public function getBonusReview()
    {
        return new CaptainForm();
    }

    public function getBonusApply()
    {
        return new CommonCaptain();
    }

    public function getCashConfig()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'class' => 'app\\plugins\\bonus\\models\\BonusCash',
            'user_class' => 'app\\plugins\\bonus\\models\\BonusCaptain',
            'user_alias' => 'bonus_user'
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
        return '队长';
    }

    public function getReviewClass($config = [])
    {
        return new BonusReview($config);
    }

    public function setBonusOrderLog($order)
    {
        \Yii::error('礼物订单~');
        \Yii::error($order);
        //分红完成
        $form = new OrderBonusForm();
        $form->order = $order;
        $form->bonusOver();
    }
}
