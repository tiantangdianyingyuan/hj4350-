<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/12/17
 * Time: 16:58
 */

namespace app\plugins\region\forms\common;

use app\forms\common\CommonAppConfig;
use app\forms\common\template\tplmsg\ShareAudiTemplate;
use app\plugins\region\events\RegionEvent;
use Overtrue\EasySms\Message;

class MsgService
{
    /**
     * @param $user
     * @param RegionEvent $event
     */
    public static function sendTpl($user, $event)
    {
        try {
            $tplMsg = new ShareAudiTemplate(
                [
                    'page' => 'plugins/region/index/index',
                    'user' => $user,
                    'reviewProject' => '区域代理',
                    'result' => $event->region->getStatusText($event->region->status),
                    'nickname' => $event->region->user->nickname,
                    'time' => $event->region->updated_at
                ]
            );
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error("发送区域模板消息失败");
            \Yii::error($exception);
        }
    }

    /**
     * @param $mobile
     * @param int $type 1:region|  2:region_level_up
     * @param $name
     * @param $rate
     */
    public static function sendSms($mobile, $type = 1, $name = 0, $rate = 0)
    {
        try {
            $smsConfig = CommonAppConfig::getSmsConfig(0);
            if (!isset($smsConfig['status']) || $smsConfig['status'] != 1) {
                throw new \Exception('短信开关尚未开启');
            }
            $message = [];
            if ($type == 1) {
                if (
                    !isset($smsConfig['region']) ||
                    !isset($smsConfig['region']['template_id']) ||
                    !isset($smsConfig['region']['name'])
                ) {
                    throw new \Exception('区域代理插件短信未设置正确x1');
                }
                $data[$smsConfig['region']['name']] = $name;
                $message = new Message(
                    [
                        'template' => $smsConfig['region']['template_id'],
                        'data' => $data
                    ]
                );
            } elseif ($type == 2) {
                if (
                    !isset($smsConfig['region_level_up']) ||
                    !isset($smsConfig['region_level_up']['template_id']) ||
                    !isset($smsConfig['region_level_up']['name']) ||
                    !isset($smsConfig['region_level_up']['number'])
                ) {
                    throw new \Exception('区域代理插件短信未设置正确x2');
                }
                $data[$smsConfig['region_level_up']['name']] = $name;
                $data[$smsConfig['region_level_up']['number']] = $rate;
                $message = new Message(
                    [
                        'template' => $smsConfig['region_level_up']['template_id'],
                        'data' => $data
                    ]
                );
            }

            $mobile && \Yii::$app->sms->module('mall')->send($mobile, $message);
        } catch (\Exception $exception) {
            \Yii::error('=====区域代理插件短信短信通知失败=====');
            \Yii::error($exception);
        }
    }
}
