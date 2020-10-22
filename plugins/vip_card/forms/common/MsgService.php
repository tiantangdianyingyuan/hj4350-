<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 10:19
 */

namespace app\plugins\vip_card\forms\common;

use app\forms\common\CommonAppConfig;
use Overtrue\EasySms\Message;

class MsgService
{
    public static function sendSms($user,$goodsName)
    {
        try {
            $smsConfig = CommonAppConfig::getSmsConfig(0);
            if (!isset($smsConfig['vipCard']) || !isset($smsConfig['vipCard']['template_id']) || !isset($smsConfig['vipCard']['name'])) {
                throw new \Exception('超级会员卡插件短信未设置正确');
            }
            $data[$smsConfig['vipCard']['name']] = $goodsName;
            $message = new Message([
                'template' => $smsConfig['vipCard']['template_id'],
                'data' => $data
            ]);
            $user->mobile && \Yii::$app->sms->module('mall')->send($user->mobile, $message);
        } catch (\Exception $exception) {
            \Yii::error('=====超级会员卡插件短信短信通知失败=====');
            \Yii::error($exception);
        }
    }
}