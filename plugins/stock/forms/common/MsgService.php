<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/12/17
 * Time: 16:58
 */

namespace app\plugins\stock\forms\common;

use app\forms\common\CommonAppConfig;
use app\forms\common\template\tplmsg\ShareAudiTemplate;
use app\plugins\stock\events\StockEvent;
use Overtrue\EasySms\Message;

class MsgService
{
    /**
     * @param $user
     * @param StockEvent $event
     */
    public static function sendTpl($user, $event)
    {
        try {
            $tplMsg = new ShareAudiTemplate([
                'page' => 'plugins/stock/index/index',
                'user' => $user,
                'reviewProject' => '股东分红',
                'result' => $event->stock->getStatusText($event->stock->status),
                'nickname' => $event->stock->user->nickname,
                'time' => $event->stock->updated_at
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error("发送股东订阅消息失败");
            \Yii::error($exception);
        }
    }

    /**
     * @param $mobile
     * @param int $type 1:stock|  2:stock_level_up
     * @param $name
     * @param $rate
     */
    public static function sendSms($mobile, $type = 1, $name = 0, $rate = 0)
    {
        try {
            $smsConfig = CommonAppConfig::getSmsConfig(0);

            $message = [];
            if ($type == 1) {
                if (!isset($smsConfig['stock']) || !isset($smsConfig['stock']['template_id'])) {
                    throw new \Exception('股东分红插件短信未设置正确x1');
                }
                $message = new Message([
                    'template' => $smsConfig['stock']['template_id'],
                    'data' => []
                ]);
            } elseif ($type == 2) {
                if (
                    !isset($smsConfig['stock_level_up']) ||
                    !isset($smsConfig['stock_level_up']['template_id']) ||
                    !isset($smsConfig['stock_level_up']['name']) ||
                    !isset($smsConfig['stock_level_up']['number'])
                ) {
                    throw new \Exception('股东分红插件短信未设置正确x2');
                }
                $data[$smsConfig['stock_level_up']['name']] = $name;
                $data[$smsConfig['stock_level_up']['number']] = $rate;
                $message = new Message([
                    'template' => $smsConfig['stock_level_up']['template_id'],
                    'data' => $data
                ]);
            }

            $mobile && \Yii::$app->sms->module('mall')->send($mobile, $message);
        } catch (\Exception $exception) {
            \Yii::error('=====股东分红插件短信短信通知失败=====');
            \Yii::error($exception);
        }
    }
}
