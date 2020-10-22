<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\demo;


use app\controllers\Controller;
use app\forms\common\CommonAppConfig;
use app\helpers\PluginHelper;
use app\models\Order;
use app\plugins\demo\forms\ApiCashForm;
use app\plugins\demo\forms\CashForm;
use Overtrue\EasySms\Message;
use yii\base\Event;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '欢迎使用',
                'route' => 'plugin/demo',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '菜单组1',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '操作1',
                        'route' => 'plugin/demo/test/index',
                    ],
                    [
                        'name' => '操作2',
                        'route' => 'plugin/demo/test/edit',
                    ],
                ],
            ],
            [
                'name' => '菜单组2',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '操作1',
                        'route' => 'plugin/demo/test2/index',
                    ],
                    [
                        'name' => '操作2',
                        'route' => 'plugin/demo/test2/edit',
                    ],
                ],
            ],
            [
                'name' => '模板消息',
                'route' => 'plugin/demo/template/template',
                'icon' => 'el-icon-star-on',
            ],
        ];
    }

    public function handler()
    {
        // 举例，订单下单事件
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            // 这里开始你的代码
            \Yii::warning('---TEST DEMO_PLUGIN_HANDLER EVENT_CREATED---');
        });
        // 举例，app message 请求事件
        \Yii::$app->on(\Yii::$app->appMessage::EVENT_APP_MESSAGE_REQUEST, function ($event) {
            \Yii::$app->appMessage->push('plugin_wxapp_test', '微信消息abc');
            \Yii::$app->appMessage->push('plugin_wxapp_test2', [
                'list' => ['aaa', 'bbb', 'ccc'],
            ]);
        });
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'demo';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '演示插件';
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
        return 'plugin/demo';
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

    public function smsSend()
    {
        $smsConfig = CommonAppConfig::getSmsConfig();
        $message = new Message([
            'template' => $smsConfig['bonus']['template_id'],
            'data' => [
                'name' => '89757',
                'time' => '89757'
            ]
        ]);
        $sms = \Yii::$app->sms->module('mall');
        foreach ($smsConfig['mobile_list'] as $mobile) {
            $sms->send($mobile, $message);
        }
    }

    /**
     * 小程序端提现聚合接口插件实现
     * @return ApiCashForm
     */
    public function getApiCashForm()
    {
        $setting = ['pay_type' => ['auto']];
        return new ApiCashForm(['setting' => $setting]);
    }

    /**
     * 聚合提现插件审核实现
     */
    public function getCashApplyForm()
    {
        return new CashForm();
    }
}
