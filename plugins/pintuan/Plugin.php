<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\pintuan;


use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\forms\common\goods\CommonGoodsDetail;
use app\helpers\PluginHelper;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\plugins\pintuan\forms\api\v2\StatisticsForm;
use app\plugins\pintuan\forms\common\CommonGoods;
use app\plugins\pintuan\forms\common\PintuanFailTemplate;
use app\plugins\pintuan\forms\common\PintuanSuccessTemplate;
use app\plugins\pintuan\forms\common\v2\SettingForm;
use app\plugins\pintuan\handler\v2\OrderCreatedHandler;
use app\plugins\pintuan\handler\v2\OrderPayEventHandler;
use app\plugins\pintuan\models\Goods;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanGoodsGroups;
use app\plugins\pintuan\models\PintuanOrderRelation;


class Plugin extends \app\plugins\Plugin
{

    public $version = '4.2.69';

    public function getMenus()
    {
        return [
            [
                'name' => '拼团设置',
                'route' => 'plugin/pintuan/mall/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/pintuan/mall/index/template',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '拼团活动',
                'route' => 'plugin/pintuan/mall/activity/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/pintuan/mall/goods/edit',
                    ],
                    [
                        'name' => '活动数据',
                        'route' => 'plugin/pintuan/mall/activity/detail',
                    ],
                    [
                        'name' => '活动数据',
                        'route' => 'plugin/pintuan/mall/activity/edit',
                    ],
                ]
            ],
            [
                'name' => '活动数据',
                'route' => 'plugin/pintuan/mall/activity/groups',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '活动数据详情',
                        'route' => 'plugin/pintuan/mall/activity/groups-orders',
                    ],
                ]
            ],
//            [
//                'name' => '机器人设置',
//                'route' => 'plugin/pintuan/mall/robot/index',
//                'icon' => 'el-icon-star-on',
//            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            // 这里开始你的代码
            if ($event->order->sign == $this->getName()) {
                $event->order->status = 1;
                $res = $event->order->save();
                if (!$res) {
                    \Yii::error('拼团订单状态更新失败');
                }
            }
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'pintuan';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '拼团';
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

    //商品详情路径
    public static function getGoodsUrl($item)
    {
        /** @var PintuanGoods $ptGoods */
        $ptGoods = PintuanGoods::find()->where(['goods_id' => $item['id']])->one();
        if ($ptGoods->pintuan_goods_id != 0) {
            $ptGoods = PintuanGoods::find()->where(['id' => $ptGoods->pintuan_goods_id])->one();
        }

        return sprintf("/plugins/pt/goods/goods?goods_id=%u", $ptGoods ? $ptGoods->goods_id : $item['id']);
    }

    public function getIndexRoute()
    {
        return 'plugin/pintuan/mall/index';
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
                'key' => 'pintuan',
                'name' => '拼团首页',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/index/index',
                // 'params' => [
                // [
                //     'key' => 'cat_id',
                //     'value' => '',
                //     'desc' => '请填写拼团分类ID,不填则显示热销',
                //     'is_required' => false,
                //     'data_type' => 'number',
                //     'page_url' => 'plugin/pintuan/mall/cats',
                //     'pic_url' => $iconBaseUrl . '/example_image/cat-id.png',
                //     'page_url_text' => '商品分类'
                // ]
                // ]
            ],
            [
                'key' => 'pintuan',
                'name' => '我的拼团',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/order/order',
            ],
            [
                'key' => 'pintuan',
                'name' => '拼团商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-pintuan.png',
                'value' => '/plugins/pt/goods/goods',
                'params' => [
                    [
                        'key' => 'goods_id',
                        'value' => '',
                        'desc' => '请填写拼团商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/pintuan/mall/activity/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    /**
     * @return OrderPayEventHandler
     * @throws \Exception
     * 获取订单支付完成事件
     */
    public function getOrderPayedHandleClass()
    {
        // TODO 判断版本
        if (version_compare(\Yii::$app->getAppVersion(), $this->version) == 1) {
            $orderPayedHandlerClass = new OrderPayEventHandler();
            \Yii::warning('进入了v2');
        } else {
            $orderPayedHandlerClass = new \app\plugins\pintuan\handler\OrderPayEventHandler();
            \Yii::warning('进入了v1');
        }

        return $orderPayedHandlerClass;
    }

    /**
     * 订单创建完成事件
     * @return \app\handlers\orderHandler\OrderCreatedHandlerClass
     */
    public function getOrderCreatedHandleClass()
    {
        $orderCreatedHandlerClass = new OrderCreatedHandler();

        return $orderCreatedHandlerClass;
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
            'is_member_price' => $setting['is_member_price']
        ]);

        return $config;
    }

    public function getGoodsData($array)
    {
        return \app\plugins\pintuan\forms\common\v2\CommonGoods::getCommon()->getDiyGoods($array);
    }

    public function getHomePage($type)
    {
        // TODO 判断版本
        if (version_compare(\Yii::$app->getAppVersion(), $this->version) == 1) {
            return \app\plugins\pintuan\forms\common\v2\CommonGoods::getCommon()->getHomePage($type);
        } else {
            return CommonGoods::getCommon()->getHomePage($type);
        }
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
            'plugin/pintuan/api/v2/order/submit'
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/pintuan-statistics/index',
        ];
    }

    public function install()
    {
        $sql = <<<EOF
alter table `zjhj_bd_pintuan_order_relation` add cancel_status tinyint(1) not NULL default '0' COMMENT '拼团订单取消状态:0.未取消|1.超出拼团总人数取消';
EOF;
        sql_execute($sql);
        return parent::install();
    }

    public function getGoodsExtra($goods)
    {
        if ($goods->sign != $this->getName()) {
            return [];
        }

        // TODO 判断版本
        if (version_compare(\Yii::$app->getAppVersion(), $this->version) == 1) {
            return \app\plugins\pintuan\forms\common\v2\CommonGoods::getCommon()->getGoodsExtra($goods);
        } else {
            return CommonGoods::getCommon()->getGoodsExtra($goods);
        }
    }

    public function hasVideoGoodsList($goods, $page, $limit)
    {
        $nowDate = date('Y-m-d H:i:s');
        $list = Goods::find()->alias('g')
            ->with(['goodsWarehouse', 'attr'])
            ->where(['g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id])
            ->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->leftJoin(['pg' => PintuanGoods::tableName()], 'pg.goods_id=g.id')
            ->andWhere(['<', 'pg.end_time', $nowDate])
            ->andWhere(['pg.pintuan_goods_id' => 0])
            // ->leftJoin(['pgg' => PintuanGoodsGroups::tableName(), 'pgg.goods_id=g.id'])
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($limit, $page)
            ->all();
        return $list;
    }

    public function templateList()
    {
        return [
            'pintuan_success_notice' => \app\plugins\pintuan\forms\common\v2\PintuanSuccessTemplate::class,
            'pintuan_fail_notice' => \app\plugins\pintuan\forms\common\v2\PintuanFailTemplate::class,
        ];
    }

    public function updateGoodsPrice($goods)
    {
        // TODO 处理拼团阶梯团价
        return true;
    }

    public function getEnableVipDiscount()
    {
        $setting = (new SettingForm())->search();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getSignCondition($where)
    {
        // 有阶梯团的商品 才在前端展示
        $pintuanGoodsIds = PintuanGoods::find()->where(['>', 'pintuan_goods_id', 0])->andWhere([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])->groupBy('pintuan_goods_id')->select('pintuan_goods_id');

        $goodsIds = PintuanGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $pintuanGoodsIds,
        ])->andWhere(['<', 'start_time', mysql_timestamp()])
            ->andWhere([
                'or',
                ['end_time' => '0000-00-00 00:00:00'],
                ['>', 'end_time', mysql_timestamp()]
            ])->select('goods_id');
        return $goodsIds;
    }

    public function supportEcard()
    {
        return true;
    }

    public function getEnableFullReduce()
    {
        $setting = (new SettingForm())->search();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }

    public function videoGoods($goods, $detail)
    {
        $form = new CommonGoodsDetail();
        $form->user = \Yii::$app->user->identity;
        $form->mall = \Yii::$app->mall;
        $form->goods = $goods;
        $newGoods = $form->getAll();

        $goodsList = (new Goods())->getGoodsGroups($goods);

        $groups = [];
        $groupsPriceMax = 0;// 所有阶梯团中最大价格
        $groupsPriceMin = 0;// 所有阶梯团中最小价格
        $goodsStock = 0;
        /** @var Goods $gItem */
        foreach ($goodsList as $gItem) {
            $form->goods = $gItem;
            $ptGoods = $form->getAll(['attr', 'price_min', 'price_max', 'share']);
            // 获取最大分销价
            $newGoods['share'] = max($newGoods['share'], $ptGoods['share']);

            $groupsPriceMax = $groupsPriceMax == 0 ? $ptGoods['price_max'] : max($ptGoods['price_max'], $groupsPriceMax);
            $groupsPriceMin = $groupsPriceMin == 0 ? $ptGoods['price_min'] : min($ptGoods['price_min'], $groupsPriceMin);
            $memberPriceMin = 0;
            $memberPriceMax = 0;

            foreach ($ptGoods['attr'] as $aItem) {
                $goodsStock += $aItem['stock'];
                if ($memberPriceMin == 0) {
                    $memberPriceMin = $aItem['price_member'];
                } else {
                    $memberPriceMin = min($memberPriceMin, $aItem['price_member']);
                }

                if ($memberPriceMax == 0) {
                    $memberPriceMax = $aItem['price_member'];
                } else {
                    $memberPriceMax = max($memberPriceMax, $aItem['price_member']);
                }
            }

            $groups[] = [
                'group_id' => $gItem->oneGroups->id,
                'groups' => $gItem->oneGroups,
                'attr' => $ptGoods['attr'],
                'price_min' => $ptGoods['price_min'],
                'price_max' => $ptGoods['price_max'],
                'member_price_min' => $memberPriceMin,
                'member_price_max' => $memberPriceMax,
            ];
        }

        $pintuanGoods = PintuanGoods::find()->andWhere(['goods_id' => $goods->id])->one();

        $array = [];
        $array['groups'] = $groups;
        $array['pintuanGoods'] = $pintuanGoods;
        return $array;
    }

    public function getOrderInfo($orderId, $order)
    {
        $pintuanOrderRelation = PintuanOrderRelation::find()->andWhere(['order_id' => $orderId])->with('pintuanOrder')->one();
        if ($pintuanOrderRelation && $pintuanOrderRelation->is_groups == 1 && $pintuanOrderRelation->is_parent == 1) {
            $data = [
                'discount_list' => [
                    'pintuan_discount' => [
                        'label' => '团长优惠',
                        'value' => $pintuanOrderRelation->pintuanOrder->preferential_price,
                    ]
                ]
            ];
            return $data;
        }
    }
}
