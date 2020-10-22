<?php

namespace app\plugins\exchange;

use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\exchange\forms\common\CommonCardGoods;
use app\plugins\exchange\forms\common\CommonSetting;
use app\plugins\exchange\handlers\HandlerRegister;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\models\ExchangeOrder;
use app\plugins\exchange\models\ExchangeRecordOrder;
use app\plugins\exchange\models\ExchangeSvipOrder;

class Plugin extends \app\plugins\Plugin
{
    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'exchange';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '兑换中心';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/exchange/goods/goods?goods_id=%u", $item['id']);
    }

    public function getMenus()
    {
        return [
            [
                'name' => '设置',
                'route' => 'plugin/exchange/mall/setting/index',
                'icon' => 'el-icon-star-on'
            ],
            [
                'name' => '兑换码管理',
                'route' => 'plugin/exchange/mall/library/list',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '兑换码详情',
                        'route' => 'plugin/exchange/mall/library/edit',
                    ],
                    [
                        'name' => '兑换码回收',
                        'route' => 'plugin/exchange/mall/library/recycle',
                    ],
                    [
                        'name' => '回收战列表',
                        'route' => 'plugin/exchange/mall/library/recycle-list',
                    ],
                ]
            ],
            [
                'name' => '兑换中心',
                'route' => 'plugin/exchange/mall/code/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '礼品卡',
                'route' => 'plugin/exchange/mall/card-goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '礼品卡详情',
                        'route' => 'plugin/exchange/mall/card-goods/edit',
                    ],
                    [
                        'name' => '出售记录',
                        'route' => 'plugin/exchange/mall/card-goods/order-log',
                    ]
                ]
            ],
            [
                'name' => '订单管理',
                'route' => 'plugin/exchange/mall/order/index',
                'icon' => 'el-icon-star-on',
            ]
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/exchange/mall/setting/index';
    }

    public function getEnableVipDiscount()
    {
        $setting = (new CommonSetting())->get();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'exchange',
                'name' => '兑换中心',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-exchange.png',
                'value' => '/plugins/exchange/index/index',
                'ignore' => [],
            ],
            [
                'key' => 'exchange',
                'name' => '礼品卡中心',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-card.png',
                'value' => '/plugins/exchange/list/list',
                'ignore' => [],
            ],
        ];
    }

    /**
     * 获取商品配置
     */
    public function getGoodsConfig()
    {
        return [];
    }


    public function getGoodsData($array)
    {
        return (new CommonCardGoods())->getDiyGoods($array);
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

    public function getIsTypePlugin()
    {
        return true;
    }

    public function getOrderConfig()
    {
        $setting = (new CommonSetting())->get();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
            'is_member_price' => $setting['is_member_price'],
        ]);
        return $config;
    }

    public function getTypeData($order)
    {
    }

    public function getGoodsDetailExtra($goods)
    {
    }

    public function getSignCondition($where)
    {
        return ExchangeGoods::find()->alias('e')->select('e.goods_id')->where([
            'l.expire_type' => 'all',
        ])->orWhere([
            'AND',
            ['l.expire_type' => 'relatively'],
            ['>=', 'l.expire_start_day', 1],
        ])->orWhere([
            'AND',
            ['l.expire_type' => 'fixed'],
            ['>', 'l.expire_end_time', date('Y-m-d H:i:s')],
            //['<', 'l.expire_start_time', date('Y-m-d H:i:s')],
        ])->andWhere([
            'l.is_delete' => 0,
            'l.is_recycle' => 0,
            'e.mall_id' => \Yii::$app->mall->id,
        ])->joinWith(['library l']);
    }

    public function changeOrderInfo($order)
    {
        //区分不同订单
        $sign = current($order['detail'])['goods']['sign'];

        if ($sign === $this->getName()) {
            $eo = ExchangeOrder::find()->where([
                'order_id' => $order['id']
            ])->one();
        } else if ($sign === 'vip_card') {
            $eo = ExchangeSvipOrder::find()->where([
                'order_id' => $order['id']
            ])->one();
        } else {
            $eo = ExchangeRecordOrder::find()->where([
                'order_id' => $order['id']
            ])->one();
        }
        if ($eo && $code = $eo->code) {
            $order['exchange_code'] = [
                'code' => $eo->code->code,
                'library_name' => $eo->code->library->name,
            ];
        }
        return $order;
    }

    //商品上下架阻断
    public function breakGoodsStatus($ids, $after)
    {
        if ($after != 1) {
            return false;
        }
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $id) {
            $exchangeGoods = ExchangeGoods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'goods_id' => $id,
            ])->one();
            if (!$exchangeGoods) {
                continue;
            }
            $libraryModel = $exchangeGoods->library;
            if (
                ($libraryModel->expire_type === 'all'
                    ||
                    $libraryModel->expire_type === 'relatively'
                    && $libraryModel->expire_start_day >= 1
                    ||
                    $libraryModel->expire_type === 'fixed'
                    && $libraryModel->expire_end_time > date('Y-m-d H:i:s')
                )
                && ($libraryModel->is_recycle == 0
                && $libraryModel->is_delete == 0)
            ) {
                continue;
            }
            throw new \Exception('兑换码库不存在，请重新选择');
        }
        return false;
    }

    public function getEnableFullReduce()
    {
        $setting = (new CommonSetting())->get();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }
}
