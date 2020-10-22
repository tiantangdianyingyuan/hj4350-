<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\fxhb;


use app\helpers\PluginHelper;
use app\plugins\fxhb\forms\api\StatisticsForm;
use app\plugins\fxhb\forms\common\CommonFxhbDb;
use app\plugins\fxhb\handle\HandlerRegister;
use app\plugins\fxhb\handle\JoinActivityHandle;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '红包活动',
                'route' => 'plugin/fxhb/mall/activity/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '自定义页面编辑',
                        'route' => 'plugin/fxhb/mall/activity/edit',
                    ],
                ]
            ],
            [
                'name' => '红包记录',
                'route' => 'plugin/fxhb/mall/activity/log',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '推荐设置',
                'route' => 'plugin/fxhb/mall/recommend/index',
                'icon' => 'el-icon-star-on',
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
        \Yii::$app->on(HandlerRegister::FXHB_JOIN_ACTIVITY, function ($event) {
            (new JoinActivityHandle())->on($event);
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'fxhb';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '拆红包';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/image';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg',
                'fxhb_none' => $imageBaseUrl . '/fxhb_none.png',
                'bg' => $imageBaseUrl . '/bg.png',
                'share_modal_bg' => $imageBaseUrl . '/share_modal_bg.png',
                'hongbao_bg' => $imageBaseUrl . '/hongbao_bg.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/fxhb/mall/activity/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/image/pick-link';

        return [
            [
                'key' => 'fxhb',
                'name' => '拆红包',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-fxhb.png',
                'value' => '/plugins/fxhb/detail/detail',
            ],
        ];
    }

    /**
     * 返回实例化后台统计数据接口
     * @return IntegralForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function getHomePage($type)
    {
        if ($type == 'mall') {
            return false;
        }
        $common = CommonFxhbDb::getCommon(\Yii::$app->mall);
        $activity = $common->getActivity('*', ['is_home_model' => 1]);

        if ($activity) {
            return [
                [
                    'picUrl' => $activity->pic_url,
                    'link' => [
                        'openType' => 'navigate',
                        'url' => '/plugins/fxhb/detail/detail'
                    ]
                ]
            ];
        }

        return [];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/fxhb-statistics/index',
        ];
    }
}
