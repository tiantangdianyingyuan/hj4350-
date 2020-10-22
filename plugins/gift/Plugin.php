<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019年10月11日 14:15:22
 */


namespace app\plugins\gift;


use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\forms\common\CommonGoods;
use app\plugins\gift\forms\common\GiftConvertTemplate;
use app\plugins\gift\forms\common\GiftFromUserTemplate;
use app\plugins\gift\forms\common\GiftToUserTemplate;
use app\plugins\gift\forms\mall\StatisticsForm;
use app\plugins\gift\handlers\HandlerRegister;
use app\plugins\gift\handlers\OrderCreatedHandler;
use app\plugins\gift\handlers\OrderPayedHandler;
use app\plugins\gift\handlers\OrderSalesHandler;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '社交送礼设置',
                'route' => 'plugin/gift/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/gift/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品管理',
                        'route' => 'plugin/gift/mall/goods/edit',
                    ],
                ]
            ],
//            [
//                'name' => '商品分类',
//                'route' => 'plugin/gift/mall/goods/categories',
//                'icon' => 'el-icon-star-on'
//            ],
            [
                'name' => '礼物记录',
                'route' => 'plugin/gift/mall/record/tribute',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '记录详情',
                        'route' => 'plugin/gift/mall/record/tribute-detail',
                    ],
                ]
            ],
            [
                'name' => '领取记录',
                'route' => 'plugin/gift/mall/record/receive',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/gift/mall/setting/template',
                'icon' => 'el-icon-star-on',
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
        return 'gift';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '社交送礼';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'scratch_bg' => $imageBaseUrl . '/step-bg.png',
                'scratch_win' => $imageBaseUrl . '/step-win.png'
            ],
        ];
    }


    public function getIndexRoute()
    {
        return 'plugin/gift/mall/setting/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';
        return [
            [
                'key' => 'gift',
                'name' => '礼物',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-gift.png',
                'value' => '/plugins/gift/index/index',
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = CommonGift::getSetting();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => $setting['is_share'],
            'is_member_price' => $setting['is_member_price']
        ]);
        return $config;
    }

    /**
     * 返回实例化后台统计数据接口
     * @return object
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
            'route' => 'mall/gift-statistics/index',
        ];
    }

    public function getSmsSetting()
    {
        return [
            'gift_lottery' => [
                'title' => '抽奖结果通知',
                'content' => '例如：模板内容：您参与的送礼物活动结果为${code}，请登录商城查看。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'code',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您参与的送礼物活动结果为${code}，请登录商城查看，则只需填写code'
                    ]
                ]
            ],
            'gift' => [
                'title' => '礼物到期提醒',
                'content' => '例如：模板内容：您收到的礼物即将到期，请及时填写收货地址。',
                'support_mch' => false,
                'loading' => false,
                'variable' => []
            ],
        ];
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/gift/goods/goods?id=%u", $item['id']);
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayedHandler(); // TODO: Change the autogenerated stub
    }

    public function getOrderSalesHandleClass()
    {
        return new OrderSalesHandler(); // TODO: Change the autogenerated stub
    }

    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedHandler(); // TODO: Change the autogenerated stub
    }

    public function templateList()
    {
        return [
            'gift_to_user' => GiftToUserTemplate::class,
            'gift_convert' => GiftConvertTemplate::class,
            'gift_form_user' => GiftFromUserTemplate::class,
        ];
    }

    public function getEnableVipDiscount()
    {
        $setting = CommonGift::getSetting();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getBlackList()
    {
        return [
            'plugin/gift/api/gift-order/order-submit',
        ];
    }


    public function getGoodsData($array)
    {
        return CommonGoods::getCommon()->getDiyGoods($array);
    }

    public function getOrderAction($actionList, $order)
    {
        if ($order->status = 0) {
            $actionList['is_show_comment'] = 0;
        }

        return $actionList;
    }

    public function getEnableFullReduce()
    {
        $setting = CommonGift::getSetting();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }
}
