<?php
/**
 * @copyright  ©2020 浙江禾匠信息科技
 * @author 风哀伤
 * @link http://www.zjhejiang.com/
 * Date Time: 2020/02/11 14:42
 */

namespace app\plugins\composition;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\plugins\composition\forms\api\GoodsForm;
use app\plugins\composition\forms\api\StatisticsForm;
use app\plugins\composition\forms\common\CommonGoods;
use app\plugins\composition\forms\common\OrderDetailForm;
use app\plugins\composition\forms\common\CommonSetting;
use app\plugins\composition\handlers\HandlerRegister;
use app\plugins\composition\models\CompositionOrder;
use app\plugins\Plugin as AppPlugin;

class Plugin extends AppPlugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '设置',
                'route' => 'plugin/composition/mall/index/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '套餐组合',
                'route' => 'plugin/composition/mall/index/list',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '固定套餐',
                        'route' => 'plugin/composition/mall/index/fixed',
                    ],
                    [
                        'name' => '搭配套餐',
                        'route' => 'plugin/composition/mall/index/goods',
                    ],
                ],
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'composition';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '套餐组合';
    }

    public function getIndexRoute()
    {
        return 'plugin/composition/mall/index/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'composition',
                'name' => '套餐组合',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-composition.png',
                'value' => '/plugins/composition/index/index',
                'ignore' => [],
            ],
            [
                'key' => 'composition',
                'name' => '套餐详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-composition.png',
                'value' => '/plugins/composition/detail/detail',
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
                'params' => [
                    [
                        'key' => 'composition_id',
                        'value' => '',
                        'desc' => '请填写套餐组合ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/composition/mall/index/list',
                        'pic_url' => $iconBaseUrl . '/example.png',
                        'page_url_text' => '套餐组合'
                    ]
                ]
            ],
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/composition-statistics/index',
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

    /**
     * @param Goods $goods
     * @return array
     * @throws \Exception
     */
    public function getGoodsDetailExtra($goods)
    {
        $form = new GoodsForm();
        return $form->getList($goods->id);
    }

    public function getOrderConfig()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        $config = new OrderConfig();
        $config->setOrder();
        $config->is_share = $setting['is_share'];
        return $config;
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
     * @param $order
     * @return array
     * 处理小程序端订单详情数据
     */
    public function changeOrderInfo($order)
    {
        $form = new OrderDetailForm();
        return $form->changeOrderInfo($order);
    }

    public function getOrderInfo($orderId, $order)
    {
        /* @var CompositionOrder[] $compositionOrder */
        $compositionOrder = CompositionOrder::find()->where(['order_id' => $orderId])->all();
        $data = [];
        $price = 0;
        foreach ($compositionOrder as $item) {
            $price += floatval($item->price);
        }
        if ($price > 0) {
            $data = [
                'discount_list' => [
                    'composition' => [
                        'label' => '套餐优惠',
                        'value' => price_format($price),
                    ],
                ],
                'print_list' => [
                    'composition' => [
                        'label' => '套餐优惠',
                        'value' => price_format($price),
                    ],
                ],
            ];
        }
        return $data;
    }

    public function getBlackList()
    {
        return [
            'plugin/composition/api/index/order-submit',
        ];
    }

    public function getGoodsData($array)
    {
        return CommonGoods::getCommon()->getDiyGoods($array);
    }

    public function getEnableFullReduce()
    {
        $setting = CommonSetting::getCommon()->getSetting();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }
}
