<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\booking;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\plugins\booking\forms\common\CommonBooking;
use app\plugins\booking\forms\common\CommonBookingGoods;
use app\plugins\booking\forms\api\StatisticsForm;
use yii\base\Event;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '预约设置',
                'route' => 'plugin/booking/mall/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '预约商品',
                'route' => 'plugin/booking/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/booking/mall/goods/edit',
                    ],
                ]
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
//         $register = new HandlerRegister();
//         $HandlerClasses = $register->getHandlers();
//         foreach ($HandlerClasses as $HandlerClass) {
//             $handler = new $HandlerClass();
//             if ($handler instanceof HandlerBase) {
//                 /** @var HandlerBase $handler */
//                 $handler->register();
//             }
//         }
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'booking';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '预约';
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

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/book/goods/goods?goods_id=%u", $item['id']);
    }

    public function getIndexRoute()
    {
        return 'plugin/booking/mall/setting';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';
        return [
            [
                'key' => 'booking',
                'name' => '预约',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-booking.png',
                'value' => '/plugins/book/index/index',
                'params' => [
                    [
                        'key' => 'cat_id',
                        'value' => '',
                        'desc' => '请填写预约分类ID,不填显示全部',
                        'is_required' => false,
                        'data_type' => 'number',
                        'page_url' => '/mall/cat/index',
                        'pic_url' => $iconBaseUrl . '/example_image/cat-id.png',
                        'page_url_text' => '分类管理'
                    ]
                ]
            ],
            [
                'key' => 'booking',
                'name' => '预约商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-booking.png',
                'value' => '/plugins/book/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写预约商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/booking/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
//            [
//                'key' => 'booking',
//                'name' => '我的预约',
//                'open_type' => '',
//                'icon' => $iconBaseUrl . '/icon-booking.png',
//                'value' => '/plugins/book/order/order',
//            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = (new CommonBooking())->getSetting(\Yii::$app->mall->id);
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_share' => $setting['is_share'],
            'is_mail' => 1,
            'support_share' => 1,
            'is_member_price' => $setting['is_member_price']
        ]);
        return $config;
    }

    public function getHomePage($type)
    {
        return CommonBookingGoods::getCommon()->getHomePage($type);
    }

    /**
     * 返回实例化后台统计数据接口
     * @return IntegralForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function getBlackList()
    {
        return [
            'plugin/booking/api/order/order-submit',
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/booking-statistics/index',
        ];
    }

    public function getGoodsData($array)
    {
        return CommonBookingGoods::getCommon()->getDiyGoods($array);
    }

    public function getEnableVipDiscount()
    {
        $setting = CommonBooking::getSetting();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getEnableFullReduce()
    {
        $setting = CommonBooking::getSetting();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }
}
