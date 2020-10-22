<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\shopping;


use app\helpers\PluginHelper;
use app\models\Cart;
use app\models\Order;
use app\plugins\shopping\forms\common\CommonShopping;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '好物圈设置',
                'route' => 'plugin/shopping/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '已购好物圈',
                'route' => 'plugin/shopping/mall/buy-order/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '已购好物圈详情',
                        'route' => 'plugin/shopping/mall/buy-order/edit',
                    ],
                ]
            ],
            [
                'name' => '想买好物圈',
                'route' => 'plugin/shopping/mall/like-goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '想买好物圈详情',
                        'route' => 'plugin/shopping/mall/like-goods/edit',
                    ],
                    [
                        'name' => '想买用户',
                        'route' => 'plugin/shopping/mall/like-goods/users',
                    ],
                ]
            ],
        ];
    }

    public function handler()
    {
        // 订单支付事件
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            $common = new CommonShopping();
            $res = $common->buyList($event->order->id);
        });
        // 订单发货事件
        \Yii::$app->on(Order::EVENT_SENT, function ($event) {
            $common = new CommonShopping();
            $res = $common->updateBuyGood($event->order->id);
        });
        // 订单确认事件
        \Yii::$app->on(Order::EVENT_CONFIRMED, function ($event) {
            $common = new CommonShopping();
            $res = $common->updateBuyGood($event->order->id);
        });
        // 添加购物车事件
        \Yii::$app->on(Cart::EVENT_CART_ADD, function ($event) {
            $common = new CommonShopping();
            foreach ($event->cartIds as $cartId) {
                $res = $common->addLikeList($cartId);
            }
        });
        // 删除购物车事件
        \Yii::$app->on(Cart::EVENT_CART_DESTROY, function ($event) {
            $common = new CommonShopping();
            $res = $common->destroyLikeList($event->cartIds);
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'shopping';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '好物圈';
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

    public function getIndexRoute()
    {
        return 'plugin/shopping/mall/setting/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        return [
        ];
    }
}
