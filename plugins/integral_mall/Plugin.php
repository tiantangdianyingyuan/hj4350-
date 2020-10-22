<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\integral_mall\forms\api\StatisticsForm;
use app\plugins\integral_mall\forms\common\CommonGoods;
use app\plugins\integral_mall\forms\common\SettingForm;
use app\plugins\integral_mall\handlers\HandlerRegister;
use app\plugins\integral_mall\models\IntegralMallOrders;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '积分商城设置',
                'route' => 'plugin/integral_mall/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/integral_mall/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/integral_mall/mall/goods/edit',
                    ],
                ],
            ],
            [
                'name' => '优惠券管理',
                'route' => 'plugin/integral_mall/mall/coupon/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '用户兑换券',
                'route' => 'plugin/integral_mall/mall/user-coupon/index',
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
        return 'integral_mall';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '积分商城';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg',
                'success' => $imageBaseUrl . '/success.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/integral_mall/mall/setting/index';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/integral_mall/goods/goods?goods_id=%u", $item['id']);
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'integral_mall',
                'name' => '积分商城',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-integral.png',
                'value' => '/plugins/integral_mall/index/index',
            ],
            [
                'key' => 'integral_mall',
                'name' => '积分商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-integral.png',
                'value' => '/plugins/integral_mall/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写积分商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/integral_mall/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理',
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getOrderConfig()
    {
        $setting = (new SettingForm())->search();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    public function getGoodsData($array)
    {
        return CommonGoods::getCommon()->getDiyGoods($array);
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
            'plugin/integral_mall/api/order/order-submit',
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/integral-statistics/mall',
        ];
    }

    public function getGoodsExtra($goods)
    {
        if ($goods->sign != $this->getName()) {
            return [];
        }
        /* @var Goods $goods */
        $content = $goods->integralMallGoods->integral_num . '积分';
        if ($goods->price > 0) {
            $content .= '+￥' . $goods->price;
        }
        return [
            'price_content' => $content,
        ];
    }

    public function getOrderInfo($orderId, $order)
    {
        $data = [];
        if ($order['sign'] == $this->getName()) {
            /** @var IntegralMallOrders $integralOrder */
            $integralOrder = IntegralMallOrders::find()->andWhere(['order_id' => $orderId, 'mall_id' => \Yii::$app->mall->id])->with('order.detail')->one();
            if ($integralOrder) {
                foreach ($order['detail'] as $key => $value) {
                    $data['price_list'][] = [
                        'order_id' => $value['id'],
                        'label' => '兑换金额',
                        'value' => $value['total_price'],
                    ];
                    $data['exchange_list'][] = [
                        'order_id' => $value['id'],
                        'label' => '兑换积分',
                        'value' => $integralOrder->integral_num,
                    ];
                }
                $data['price_name'] = '积分';
                $data['exchange_count'] = $integralOrder->integral_num;
                $data['print_list'] = [
                    'integral_mall' => [
                        'label' => '积分',
                        'value' => $integralOrder->integral_num
                    ]
                ];
            }
        }

        return $data;
    }

    public function supportEcard()
    {
        return true;
    }
}
