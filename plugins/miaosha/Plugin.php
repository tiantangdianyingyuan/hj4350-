<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */

namespace app\plugins\miaosha;

use app\forms\api\order\OrderException;
use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\plugins\miaosha\forms\api\StatisticsForm;
use app\plugins\miaosha\forms\api\v2\IndexForm;
use app\plugins\miaosha\forms\common\CommonGoods;
use app\plugins\miaosha\forms\common\v2\GoodsDestroyEvent;
use app\plugins\miaosha\forms\common\v2\SettingForm;
use app\plugins\miaosha\models\MiaoshaActivitys;
use app\plugins\miaosha\models\MiaoshaGoods;

class Plugin extends \app\plugins\Plugin
{
    public $version = '4.2.69';
    private $pluginSetting;

    public function getMenus()
    {
        return [
            [
                'name' => '秒杀设置',
                'route' => 'plugin/miaosha/mall/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '秒杀活动',
                'route' => 'plugin/miaosha/mall/activity/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '活动编辑',
                        'route' => 'plugin/miaosha/mall/activity/edit',
                    ],
                    [
                        'name' => '活动详情',
                        'route' => 'plugin/miaosha/mall/activity/detail',
                    ],
                ],
            ],
            [
                'name' => '活动数据',
                'route' => 'plugin/miaosha/mall/activity/data',
                'icon' => 'el-icon-star-on',
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
        \Yii::$app->on(Goods::EVENT_DESTROY, function ($event) {
            $form = new GoodsDestroyEvent();
            $form->goods = $event->goods;
            $form->destroy();
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'miaosha';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '整点秒杀';
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/miaosha/goods/goods?id=%u", $item['id']);
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'ms_goods_bg' => $imageBaseUrl . '/ms_goods_bg.png',
                'ms_advance_null' => $imageBaseUrl . '/ms-advance-null.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/miaosha/mall/index';
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
                'key' => 'miaosha',
                'name' => '秒杀首页',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-miaosha.png',
                'value' => '/plugins/miaosha/advance/advance',
            ],
            [
                'key' => 'miaosha',
                'name' => '秒杀商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-miaosha.png',
                'value' => '/plugins/miaosha/goods/goods',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => '请填写秒杀场次ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/miaosha/mall/activity/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表',
                    ],
                    [
                        'key' => 'is_activity',
                        'value' => 1,
                        'desc' => '判断是秒杀活动ID还是场次ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'is_show' => false,
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    public function getCartList()
    {
        $form = new IndexForm();
        $res = $form->getCartList();
        return $res;
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
        return \app\plugins\miaosha\forms\common\v2\CommonGoods::getCommon()->getDiyGoods($array);
    }

    public function getHomePage($type)
    {
        // TODO 判断版本
        if (version_compare(\Yii::$app->getAppVersion(), $this->version) == 1) {
            return \app\plugins\miaosha\forms\common\v2\CommonGoods::getCommon()->getHomePage($type);
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

    public function getOrderCreatedHandleClass()
    {
        return new \app\plugins\miaosha\handler\v2\OrderCreatedEventHandler();
    }

    public function getBlackList()
    {
        return [
            'plugin/miaosha/api/v2/order/submit',
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/miaosha-statistics/index',
        ];
    }

    public function install()
    {
        return parent::install();
    }

    public function getSignCondition($where)
    {
        $activityIds = MiaoshaActivitys::find()->where(['status' => 1, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->select('id');
        $miaoshaGoodsList = MiaoshaGoods::find()
            ->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'activity_id' => $activityIds,
            ])
            ->andWhere([
                'or',
                ['>', 'open_date', date('Y-m-d')],
                [
                    'and',
                    ['open_date' => date('Y-m-d')],
                    ['>=', 'open_time', date('H')],
                ],
            ])->select('goods_id');
        return $miaoshaGoodsList;
    }

    public function hasVideoGoodsList($goods, $page, $limit)
    {
        $nowDate = date('Y-m-d');
        $H = date('H');
        $list = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr'])->where([
            'g.sign' => $goods->sign, 'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id,
        ])->andWhere(['!=', 'g.id', $goods->id])
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id')
            ->andWhere(['!=', 'gw.video_url', ''])
            ->leftJoin(['mg' => MiaoshaGoods::tableName()], 'mg.goods_id=g.id')
            ->andWhere(
                [
                    'and',
                    ['=', 'mg.open_date', $nowDate],
                    ['=', 'mg.open_time', $H],
                ]
            )
            ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
            ->groupBy('g.goods_warehouse_id')
            ->apiPage($limit, $page)
            ->all();
        return $list;
    }

    public function getEnableVipDiscount()
    {
        $setting = (new SettingForm())->search();
        return $setting['svip_status'] == 0 ? false : true;
    }

    public function getPluginSetting()
    {
        if ($this->pluginSetting !== null) return $this->pluginSetting;
        $this->pluginSetting = (new SettingForm())->search();
        return $this->pluginSetting;
    }

    public function isGoodsEnableMemberPrice($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_member_price'] ? true : false;
    }

    public function isGoodsEnableVipPrice($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['svip_status'] ? true : false;
    }

    public function isGoodsEnableIntegral($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_integral'] ? true : false;
    }

    public function isGoodsEnableCoupon($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_coupon'] ? true : false;
    }

    public function isGoodsEnableFullReduce($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_full_reduce'] ? true : false;
    }

    public function checkGoods($goods, $item)
    {
        $miaoshaGoods = MiaoshaGoods::findOne([
            'goods_id' => $goods->id,
            'open_date' => date('Y-m-d', time()),
            'open_time' => date('H', time())
        ]);
        if (!$miaoshaGoods) {
            throw new OrderException('秒杀商品不存在');
        }

        if ($miaoshaGoods->open_date != date('Y-m-d') || $miaoshaGoods->open_time != date('H')) {
            throw new OrderException('秒杀活动未开始或已结束');
        }

        $activity = MiaoshaActivitys::find()->where([
            'status' => 1,
            'mall_id' => \Yii::$app->mall->id,
            'id' => $miaoshaGoods->activity_id,
            'is_delete' => 0,
        ])->one();
        if (!$activity) {
            throw new OrderException('当前秒杀活动已结束');
        }

        $buyCount = OrderDetail::find()->where([
            'goods_id' => $miaoshaGoods->goods_id,
        ])->joinWith(['order' => function ($query) {
            $query->andWhere([
                'user_id' => \Yii::$app->user->id,
                'is_pay' => 1
            ]);
        }])->groupBy('order_id')->count();

        if ($goods->confine_order_count != -1 && $buyCount >= $goods->confine_order_count) {
            throw new OrderException('超出购买限单(' . $goods->confine_order_count . ')次数');
        }
    }

    public function isGoodsEnableAddressLimit($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_territorial_limitation'] ? true : false;
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

    public function isEnablePriceEnable($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_offer_price'] ? true : false;
    }
}
