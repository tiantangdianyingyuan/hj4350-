<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/4
 * Time: 17:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common;


use app\models\Model;
use app\plugins\Plugin;

class CommonSms extends Model
{
    public $mall;

    public static function getCommon($mall = null)
    {
        $model = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $model->mall = $mall;
        return $model;
    }

    public function getSetting()
    {
        $setting = [
            'order' => [
                'title' => '订单支付提醒设置',
                'content' => '例如：模板内容：您有一条新的订单，订单号：89757，请登录商城后台查看。',
                'support_mch' => true,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您有一个新的订单，订单号：${order}，则只需填写order'
                    ]
                ]
            ],
            'order_refund' => [
                'title' => '订单退款提醒设置',
                'content' => '例如：模板内容：您有一条新的退款订单，请登录商城后台查看。',
                'support_mch' => true,
                'loading' => false,
                'variable' => []
            ],
            'captcha' => [
                'title' => '发送短信验证码设置',
                'content' => '例如：模板内容：您的验证码为89757，请勿告知他人。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的验证码为${code}，请勿告知他人。，则只需填写code'
                    ]
                ]
            ],
        ];
        try {
            $plugins = \Yii::$app->plugin->list;
            foreach ($plugins as $plugin) {
                $pluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
                /** @var Plugin $pluginObject */
                if (!class_exists($pluginClass)) {
                    continue;
                }
                $object = new $pluginClass();
                if (method_exists($object, 'getSmsSetting')) {
                    $setting = array_merge($setting, $object->getSmsSetting());
                }
            }
        } catch (\Exception $exception) {
        }
        return $setting;
    }
}
