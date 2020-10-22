<?php

/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */

namespace app\plugins;

use app\forms\OrderConfig;
use app\handlers\orderHandler\OrderCanceledHandlerClass;
use app\handlers\orderHandler\OrderChangePriceHandlerClass;
use app\handlers\orderHandler\OrderCreatedHandlerClass;
use app\handlers\orderHandler\OrderPayedHandlerClass;
use app\handlers\orderHandler\OrderSalesHandlerClass;
use app\models\Goods;
use app\models\User;

abstract class Plugin
{
    protected static $instance;
/**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    abstract public function getName();
/**
     * 插件显示名称
     * @return string
     */
    abstract public function getDisplayName();
/**
     * 插件安装执行代码
     * @return mixed
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件更新执行代码
     * @return mixed
     */
    public function update()
    {
        return true;
    }

    /**
     * 插件卸载执行代码
     * @return mixed
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件安装之前
     */
    public function beforeInstall()
    {
    }

    /**
     * 插件安装之后
     */
    public function afterInstall()
    {
    }

    /**
     * 插件更新之前
     */
    public function beforeUpdate()
    {
    }

    /**
     * 插件更新之后
     */
    public function afterUpdate()
    {
    }

    /**
     * 插件卸载之前
     */
    public function beforeUninstall()
    {
    }

    /**
     * 插件卸载之后
     */
    public function afterUninstall()
    {
    }


    /**
     * 获取插件菜单列表
     * @return array
     */
    public function getMenus()
    {
        return [];
    }

    public function handler()
    {
    }

    /**
     * 插件的小程序端配置，小程序端可使用getApp().config(e => { e.plugin.xxx });获取配置，xxx为插件唯一id
     * @return array
     */
    public function getAppConfig()
    {
        return [];
    }

    /**
     * 获取插件入口路由
     * @return string|null
     */
    public function getIndexRoute()
    {
        return null;
    }

    /**
     * @return string 获取插件图标
     */
    public function getIconUrl()
    {
        $default = \Yii::$app->request->getBaseUrl() . '/statics/img/common/unknown-plugin-icon.png';
        $fileName = $this->getName() . '/icon.png';
        if (file_exists(\Yii::$app->basePath . '/plugins/' . $fileName)) {
            if (\Yii::$app->request->baseUrl == '/web') {
                $baseUrl = '';
            } else {
                $baseUrl = dirname(\Yii::$app->request->baseUrl);
                $baseUrl = rtrim($baseUrl, '/');
            }
            $url = $baseUrl . '/plugins/' . $fileName;
            return $url ? $url : $default;
        } else {
            return $default;
        }
    }

    /**
     * @param null $ext
     * @return string 获取插件统计图标
     */
    public function getStatisticIconUrl($ext = null)
    {
        $default = \Yii::$app->request->getBaseUrl() . '/statics/img/common/unknown-plugin-icon.png';
        $fileName = 'pl_icon_' . $this->getName() . $ext . '.png';
        if (file_exists(\Yii::$app->basePath . '/web/statics/img/mall/statistic/plugin/' . $fileName)) {
            if (\Yii::$app->request->baseUrl == '/web') {
                $baseUrl = '';
            } else {
                $baseUrl = dirname(\Yii::$app->request->baseUrl);
                $baseUrl = rtrim($baseUrl, '/');
            }
            $url = $baseUrl . '/web/statics/img/mall/statistic/plugin/' . $fileName;
            return $url ? $url : $default;
        } else {
            return $default;
        }
    }

    /**
     * @return string 获取插件的详细描述。
     */
    public function getContent()
    {
        return '';
    }

    /**
     * @return false|string|null
     */
    public function getVersionFileContent()
    {
        $versionFile = \Yii::$app->basePath . '/plugins/' . static::getName() . '/version';
        if (file_exists($versionFile)) {
            return file_get_contents($versionFile);
        }
        return null;
    }

    /**
     * 插件可共用的跳转链接
     * @return array
     */
    public function getPickLink()
    {
        return [];
    }

    /**
     * 插件可设置的转发信息的页面链接
     */
    public function getShareContentSetting()
    {
        return [];
    }

    /**
     * 插件可设置标题的页面链接
     */
    public function getPageTitle()
    {
        return [];
    }

    /**
     * 插件可用于展示页面信息
     * @return array
     */
    public function getShowPageInfo()
    {
        return [];
    }

    /**
     * 获取商城顶部导航按钮
     * @return null|array 返回格式: ['name'=>'名称','url'=>'链接','new_window'=>'true|false, 是否新窗口打开']
     */
    public function getHeaderNav()
    {
        return null;
    }

    /**
     * @return OrderConfig
     * @throws \Exception
     * 获取插件的相关配置 例如订单是否分销、是否短信提醒、是否邮件提醒、是否小票打印等
     */
    public function getOrderConfig()
    {
        return new OrderConfig();
    }

    /**
     * @return bool
     * 判断是否是平台 例如微信平台，支付宝平台
     */
    public function getIsPlatformPlugin()
    {
        return false;
    }

    /**
     * @return OrderPayedHandlerClass
     * 重改订单支付完成事件
     */
    public function getOrderPayedHandleClass()
    {
        return new OrderPayedHandlerClass();
    }

    /**
     * @return OrderCreatedHandlerClass
     * 重改订单创建事件
     */
    public function getOrderCreatedHandleClass()
    {
        return new OrderCreatedHandlerClass();
    }

    /**
     * @return OrderCanceledHandlerClass
     * 重改订单取消事件
     */
    public function getOrderCanceledHandleClass()
    {
        return new OrderCanceledHandlerClass();
    }

    /**
     * @return OrderSalesHandlerClass
     * 重改订单售后事件
     */
    public function getOrderSalesHandleClass()
    {
        return new OrderSalesHandlerClass();
    }

    /* @return OrderChangePriceHandlerClass
     * 重改订单创建事件
     */
    public function getOrderChangePriceHandlerClass()
    {
        return new OrderChangePriceHandlerClass();
    }

    /**
     * @param string $type mall--后台数据|api--前端数据
     * @return null
     * @throws \Exception
     * 获取首页布局数据
     */
    public function getHomePage($type)
    {
        return null;
    }

    /**
     * @return bool
     * 初始化统计数据
     * @throws \Exception
     */
    public function initData()
    {
        return true;
    }

    /**
     * 黑名单 路由
     * @return array
     */
    public function getBlackList()
    {
        return [];
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserInfo($user)
    {
        return [];
    }

    /**
     * @return array
     * 获取统计菜单
     */
    public function getStatisticsMenus()
    {
        return [];
    }

    public function getSignCondition($where)
    {
        return false;
    }

    public function templateSender()
    {
        throw new \Exception('暂不支持订阅消息发送');
        return null;
    }

    /**
     * @param $orderId
     * @param $order
     * @return array ['print_list' => ['插件的键' => ['label' => '文字名称', 'value' => '名称值']]]
     * 注：此方法用于获取订单的额外信息
     * print_list--用于打印小票中
     */
    public function getOrderInfo($orderId, $order)
    {
        return [];
    }

    /**
     * @return array
     * 不支持的功能
     */
    public function getNotSupport()
    {
        return [];
    }

    /**
     * @param Goods $goods
     * @return array
     * 小程序端商品列表商品额外的信息
     */
    public function getGoodsExtra($goods)
    {
        return [];
    }

    /**
     * @return array
     * 订阅消息发送的列表
     */
    public function templateList()
    {
        return [];
    }

    public function updateGoodsPrice($goods)
    {
        return true;
    }

    /**
     * @return bool
     * 判断是否是商品类型 例如电子卡密
     */
    public function getIsTypePlugin()
    {
        return false;
    }

    /**
     * 订单相关显示操作
     * @return array
     */
    public function getOrderAction($actionList, $order)
    {
        return $actionList;
    }

    public function __call($name, $arguments)
    {
        throw new \Exception('插件没更新');
    }

    /**
     * @return bool
     * 是否支持电子卡密
     */
    public function supportEcard()
    {
        return false;
    }

    /**
     * @return bool
     * 是否需要审核
     */
    public function needCheck()
    {
        return false;
    }

    /**
     * @return bool
     * 是否需要提现操作
     */
    public function needCash()
    {
        return false;
    }

    /**
     * @return string
     * 身份名称
     */
    public function identityName()
    {
        return '';
    }

    /**
     * @return array
     * 特殊的链接需要判断之后才决定是否支持
     */
    public function getSpecialNotSupport()
    {
        return [];
    }

    /**
     * @param array $config
     * @return array
     * @throws \Exception
     * 获取处理手机端审核消息的方法
     */
    public function getReviewClass($config = [])
    {
        throw new \Exception('暂时支持');
    }

    public function getOrderExportFields()
    {
        return [];
    }

    public function getOrderExportData($params)
    {
        return [];
    }

    //商品上下架阻断
    public function breakGoodsStatus($ids, $after)
    {
        return false;
    }
}
