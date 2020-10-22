<?php

/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/1
 * Time: 16:07
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community;

use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\community\forms\api\cash\CommunityFinanceCashForm;
use app\plugins\community\forms\api\cash\CommunityFinanceConfig;
use app\plugins\community\forms\api\StatisticsForm;
use app\plugins\community\forms\common\CommonMiddleman;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\common\CommunityReview;
use app\plugins\community\forms\common\PickUpTemplate;
use app\plugins\community\forms\mall\cash\CommunityCashApply;
use app\plugins\community\handlers\HandlerRegister;
use app\plugins\community\handlers\OrderCreatedHandlerClass;
use app\plugins\community\handlers\OrderPayedHandlerClass;
use app\plugins\community\models\CommunityOrder;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '设置',
                'route' => 'plugin/community/mall/setting/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '招募团长',
                'route' => 'plugin/community/mall/middleman/recruit',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '团长管理',
                'route' => 'plugin/community/mall/middleman/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '团长详情',
                        'route' => 'plugin/community/mall/middleman/detail',
                    ],
                ],
            ],
            [
                'name' => '社区团购',
                'route' => 'plugin/community/mall/activity/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '添加活动',
                        'route' => 'plugin/community/mall/activity/edit',
                    ],
                    [
                        'name' => '编辑活动商品',
                        'route' => 'plugin/community/mall/activity/edit-activity-goods',
                    ],
                    [
                        'name' => '活动详情',
                        'route' => 'plugin/community/mall/activity/detail',
                    ],
                ],
            ],
            [
                'name' => '团购订单',
                'route' => 'plugin/community/mall/order/index',
                'icon' => 'el-icon-star-on',
                // 'action' => [
                //     [
                //         'name' => '订单详情',
                //         'route' => 'plugin/community/mall/order/detail',
                //     ],
                // ]
            ],
//            [
            //                'name' => '虚拟用户设置',
            //                'route' => 'plugin/community/mall/robot/index',
            //                'icon' => 'el-icon-star-on',
            //            ],
            [
                'name' => '消息提醒',
                'route' => 'plugin/community/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
            $this->getStatisticsMenus(false),
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'community';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '社区团购';
    }

    public function getIndexRoute()
    {
        return 'plugin/community/mall/setting/setting';
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

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';
        return [
            [
                'key' => 'community',
                'name' => '社区团购',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-community.png',
                'value' => '/plugins/community/list/list',
            ],
            [
                'key' => 'community',
                'name' => '招募令',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-recruit.png',
                'value' => '/plugins/community/recruit/recruit',
            ],
            [
                'key' => 'community',
                'name' => '团长端',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-captain.png',
                'value' => '/plugins/community/index/index',
            ],
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'pic_url' => $this->getStatisticIconUrl(),
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/community-statistics/index',
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

    public function templateList()
    {
        return [
            'pick_up_tpl' => PickUpTemplate::class,
        ];
    }

    public function getOrderInfo($orderId, $order)
    {
        $order = CommunityOrder::findOne(['order_id' => $orderId, 'is_delete' => 0]);
        if (empty($order)) {
            return [];
        }
        $data['extra'] = [
            'profit_price' => $order->profit_price,
        ];
        if ($order && $order->discount_price > 0) {
            $data['discount_list'][] = [
                'order_id' => $orderId,
                'label' => '满减',
                'value' => $order->discount_price,
            ];
        }
        return $data;
    }

    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedHandlerClass();
    }

    public function getOrderConfig()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_share' => $setting['is_share'],
            'is_mail' => 1,
            'support_share' => 1,
        ]);
        return $config;
    }

    /**
     * 小程序端提现聚合接口插件实现
     * @return CommunityFinanceCashForm
     */
    public function getApiCashForm()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        return new CommunityFinanceCashForm(['setting' => $setting]);
    }

    /**
     * 聚合提现插件审核实现
     */
    public function getCashApplyForm()
    {
        return new CommunityCashApply();
    }

    /**
     * 小程序端获取提现配置
     */
    public function getFinanceConfig()
    {
        return new CommunityFinanceConfig();
    }

    public function getCashConfig()
    {
        return [
            'name' => $this->getDisplayName(),
            'key' => $this->getName(),
            'finance' => true, // 提现存储在聚合中
        ];
    }

    public function getUserInfo($user)
    {
        $common = CommonMiddleman::getCommon();
        $middleman = $common->getConfig($user->id);
        if (!$middleman || $middleman->status != 1) {
            return [];
        }
        return [
            'community' => $common->getMiddleman($middleman),
        ];
    }

    public function getSpecialNotSupport()
    {
        $path = '/plugins/community/index/index';
        $res = [
            'user_center' => [
                $path,
            ],
        ];
        if (!\Yii::$app->user->isGuest) {
            $common = CommonMiddleman::getCommon();
            $middleman = $common->getConfig(\Yii::$app->user->id);
            if ($middleman && $middleman->status == 1) {
                $res = [];
            }
        }
        return $res;
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
        return '团长';
    }

    public function getReviewClass($config = [])
    {
        return new CommunityReview($config);
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayedHandlerClass();
    }

    public function getOrderExportFields()
    {
        return [
            [
                'key' => 'group_name',
                'value' => '团长姓名',
            ],
            [
                'key' => 'group_profit',
                'value' => '团长利润',
            ],
            [
                'key' => 'group_mobile',
                'value' => '团长手机号',
            ],
        ];
    }

    public function getOrderExportData($params)
    {
        $communityOrder = CommunityOrder::find()->andWhere(['order_id' => $params['order_id']])->with('middleman')->one();
        $array = [
            'group_name' => '',
            'group_profit' => 0,
            'group_mobile' => '',
        ];
        if ($communityOrder) {
            $array['group_name'] = $communityOrder->middleman->name;
            $array['group_mobile'] = $communityOrder->middleman->mobile;
            $array['group_profit'] = $communityOrder->profit_price;
        }
        return $array;
    }
}
