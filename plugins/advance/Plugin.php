<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\advance;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\models\GoodsWarehouse;
use app\plugins\advance\forms\api\GoodsForm;
use app\plugins\advance\forms\common\CommonGoods;
use app\plugins\advance\forms\common\SettingForm;
use app\plugins\advance\handlers\HandlerRegister;
use app\plugins\advance\models\AdvanceGoods;
use app\plugins\advance\models\AdvanceOrder;
use app\plugins\advance\models\Goods;
use app\plugins\advance\models\TailMoneyTemplate;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '预售设置',
                'route' => 'plugin/advance/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/advance/mall/setting/template',
                'icon' => 'el-icon-star-on',
            ],
//            [
            //                'name' => '轮播图',
            //                'route' => 'plugin/advance/mall/setting/banner',
            //                'icon' => 'el-icon-star-on',
            //            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/advance/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/advance/mall/goods/edit',
                    ],
                ],
            ],
            [
                'name' => '定金订单',
                'route' => 'plugin/advance/mall/deposit-order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/advance/mall/deposit-order/detail',
                    ],
                ],
            ],
            [
                'name' => '尾款订单',
                'route' => 'plugin/advance/mall/order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/advance/mall/order/detail',
                    ],
                ],
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
        return $this;
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'advance';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '商品预售';
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
        return 'plugin/advance/mall/setting/index';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/advance/detail/detail?id=%u", $item['id']);
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
                'key' => 'advance',
                'name' => '我的预定',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/advance.png',
                'value' => '/plugins/advance/order/order',
            ],
            [
                'key' => 'advance',
                'name' => '预售首页',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/advance.png',
                'value' => '/plugins/advance/index/index',
            ],
            [
                'key' => 'advance',
                'name' => '预售商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/advance.png',
                'value' => '/plugins/advance/detail/detail',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => '请填写预售商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/advance/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品管理',
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '预售',
                        'relation_id' => 0,
                        'is_edit' => 0,
                    ],
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yushou-bg.png',
                    ],
                ],
                'key' => $plugin->getName(),
            ];
        } elseif ($type == 'api') {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();
            $data = [];
            if ($res['code'] == 0) {
                $newList = [];
                foreach ($res['data']['list'] as $item) {
                    unset($item['attr']);
                    $item['is_level'] = intval($item['is_level']);
                    $newList[] = $item;
                }
                $data['list'] = $newList;
            }
            return $data;
        }
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
            'is_member_price' => $setting['is_member_price'],
        ]);

        return $config;
    }

    public function getGoodsData($array)
    {
        return CommonGoods::getCommon()->getDiyGoods($array);
    }

    public function getBlackList()
    {
        return [
            'plugin/advance/api/order/order-submit',
        ];
    }

    public function getSmsSetting()
    {
        return [
            'tailMoney' => [
                'title' => '尾款支付短信通知',
                'content' => '例如：模板内容：您预定的${name}商品已经开购，请尽快支付',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容: "您预定的${name}商品已经开购，请尽快支付"，则需填写name',
                    ],
                ],
            ],
        ];
    }

    //获取定金金额
    public function getAdvance($order_id, $order_no)
    {
        return AdvanceOrder::findOne([
            'user_id' => \Yii::$app->user->id,
            'mall_id' => \Yii::$app->mall->id,
            'order_id' => $order_id,
            'order_no' => $order_no,
            'is_pay' => 1,
            'is_cancel' => 0,
            'is_refund' => 0,
            'is_delete' => 0,
        ]);
    }

    public function getOrderInfo($orderId, $order)
    {
        $advanceOrder = AdvanceOrder::findOne(['order_id' => $orderId]);
        if ($advanceOrder) {
            $data = [
                'discount_list' => [
                    'advance_discount' => [
                        'label' => '定金抵扣',
                        'value' => $advanceOrder->swell_deposit * $advanceOrder->goods_num,
                    ],
                    'advance_ladder_discount' => [
                        'label' => '活动优惠',
                        'value' => $advanceOrder->preferential_price,
                    ],
                ],
                'print_list' => [
                    'advance_discount' => [
                        'label' => '定金抵扣',
                        'value' => $advanceOrder->swell_deposit * $advanceOrder->goods_num,
                    ],
                    'advance_ladder_discount' => [
                        'label' => '活动优惠',
                        'value' => $advanceOrder->preferential_price,
                    ],
                ],
            ];
            return $data;
        }
    }

    public function getSignCondition($where)
    {
        $advanceGoodsList = AdvanceGoods::find()
            ->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->andWhere($where)->select('goods_id');
        return $advanceGoodsList;
    }

    public function hasVideoGoodsList($goods, $page, $limit)
    {
        /* @var Goods $goods */
        $list = Goods::find()->alias('g')->where(['g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => $goods->mall_id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id = g.goods_warehouse_id')
            ->andWhere(['not', ['gw.video_url' => '']])->andWhere(['not', ['g.id' => $goods->id]])
            ->leftJoin(['ag' => AdvanceGoods::tableName()], 'g.id = ag.goods_id')
            ->andWhere(['<=', 'ag.start_prepayment_at', date('Y-m-d H:i:s', time())])
            ->andWhere(['>=', 'ag.end_prepayment_at', date('Y-m-d H:i:s', time())])
            ->page($pagination, $limit, $page)
            ->all();

        return $list;
    }

    public function templateList()
    {
        return [
            'pay_advance_balance' => TailMoneyTemplate::class,
        ];
    }

    public function getEnableVipDiscount()
    {
        $setting = (new SettingForm())->search();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getEnableFullReduce()
    {
        $setting = (new SettingForm())->search();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }
}
