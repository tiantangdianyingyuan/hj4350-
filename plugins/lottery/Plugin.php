<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\lottery;

use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\plugins\lottery\forms\api\StatisticsForm;
use app\plugins\lottery\forms\common\CommonLottery;
use app\plugins\lottery\forms\common\CommonLotteryGoods;
use app\plugins\lottery\forms\common\LotteryTemplate;
use app\plugins\lottery\handlers\HandlerRegister;
use app\plugins\lottery\models\Goods;

class Plugin extends \app\plugins\Plugin
{

    public function getMenus()
    {
        return [
            [
                'name' => '基本配置',
                'route' => 'plugin/lottery/mall/setting',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '奖品列表',
                'route' => 'plugin/lottery/mall/lottery/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '奖品编辑',
                        'route' => 'plugin/lottery/mall/lottery/edit',
                    ],
                    [
                        'name' => '奖品编辑',
                        'route' => 'plugin/lottery/mall/lottery/info',
                    ],
                ]
            ],
            [
                'name' => '轮播图',
                'route' => 'plugin/lottery/mall/banner',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '赠品订单',
                'route' => 'plugin/lottery/mall/order',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '订单详情',
                        'route' => 'plugin/lottery/mall/order/detail',
                    ],
                ]
            ],
            [
                'name' => '消息通知',
                'route' => 'plugin/lottery/mall/lottery/template',
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
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'lottery';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '幸运抽奖';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'lottery_box' => $imageBaseUrl . '/lottery-box.png',
                'lottery_boxbg' => $imageBaseUrl . '/lottery-boxbg.png',
            ]
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/lottery/mall/lottery/index';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'lottery',
                'name' => '幸运抽奖',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-lottery.png',
                'value' => '/plugins/lottery/index/index',
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
            [
                'key' => 'lottery',
                'name' => '抽奖商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-lottery.png',
                'value' => '/plugins/lottery/goods/goods',
                'params' => [
                    [
                        'key' => 'lottery_id',
                        'value' => '',
                        'desc' => '请填写抽奖商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => 'plugin/lottery/mall/lottery',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '奖品列表'
                    ]
                ],
                'ignore' => [PickLinkForm::IGNORE_TITLE, PickLinkForm::IGNORE_NAVIGATE],
            ],
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

    public function getOrderConfig()
    {
        $setting = CommonLottery::getSetting();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => 0,
            'support_share' => 1,
        ]);
        return $config;
    }

    public function initData()
    {
        $form = new StatisticsForm();
        $form->initData();
        return true;
    }

    public function getBlackList()
    {
        return [
            'plugin/lottery/api/lottery/order-submit',
            'plugin/lottery/api/lottery/detail'
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/lottery-statistics/index',
        ];
    }

    public function hasVideoGoodsList(\app\models\Goods $goods, $page, $limit): array
    {
        $query = Goods::find()->alias('g')->where([
            'AND',
            ['g.mall_id' => \Yii::$app->mall->id],
            ['g.is_delete' => 0],
            ['not', ['g.id' => $goods->id]],
        ])->innerJoinWith(['lotteryGoods l' => function ($query) {
            $query->where([
                'AND',
                ['l.mall_id' => \Yii::$app->mall->id],
                ['l.is_delete' => 0],
                ['l.status' => 1],
                ['l.type' => 0],
                ['<=', 'l.start_at', date('Y-m-d H:i:s')],
                ['>=', 'l.end_at', date('Y-m-d H:i:s')]
            ]);
        }])->innerJoinWith(['goodsWarehouse gw' => function ($query) {
            $query->where(['<>', 'gw.video_url', '']);
        }]);
        return $query->page($pagination, $limit)->all();
    }

    public function getGoodsData($array)
    {
        return CommonLotteryGoods::getCommon()->getDiyGoods($array);
    }

    public function install()
    {
        $sql = <<<EOF
        -- v1.0.4
ALTER TABLE `zjhj_bd_lottery` ADD COLUMN `buy_goods_id` int(11) NOT NULL COMMENT '购买商品id' AFTER `code_num`;
EOF;
        sql_execute($sql);
        return parent::update();
    }

    public function templateList()
    {
        return [
            'lottery_tpl' => LotteryTemplate::class,
        ];
    }

    public function supportEcard()
    {
        return true;
    }
}
