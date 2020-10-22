<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/4
 * Time: 11:37
 */

namespace app\plugins\bonus\handlers;

use app\forms\common\CommonAppConfig;
use app\forms\common\template\tplmsg\ShareAudiTemplate;
use app\handlers\HandlerBase;
use app\models\Share;
use app\models\User;
use app\plugins\bonus\events\CaptainEvent;
use app\plugins\bonus\events\MemberEvent;
use app\plugins\bonus\forms\common\CommonCaptain;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusMembers;
use Overtrue\EasySms\Message;

class BecomeCaptainHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(BonusCaptain::EVENT_BECOME, function ($event) {
            /**
             * @var CaptainEvent $event
             */
            $user = User::findOne(['id' => $event->captain->user_id, 'mall_id' => $event->captain->mall_id, 'is_delete' => 0]);
            if (($event->captain->status == 1 || $event->captain->status == 2) && $event->captain->is_delete == 0) {
                $this->send($user, $event);
            }

            if ($event->parentId) {
                $user = User::findOne(['id' => $event->parentId, 'mall_id' => $event->captain->mall_id, 'is_delete' => 0]);
                $this->send($user, $event);
                $this->sendSms($event->parentId, $event);
            }

            //成为队长触发队长升级事件
            $e = new MemberEvent([
                'captain' => $event->captain
            ]);
            \Yii::$app->trigger(BonusMembers::UPDATE_LEVEL, $e);
        });
    }

    private function send($user, $event)
    {
        try {
            $tplMsg = new ShareAudiTemplate([
                'page' => 'plugins/bonus/index/index',
                'user' => $user,
                'reviewProject' => '团队分红',
                'result' => $event->captain->getStatusText($event->captain->status),
                'nickname' => $event->captain->user->nickname,
                'time' => $event->captain->updated_at
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error("发送团队分红订阅消息失败");
            \Yii::error($exception);
        }
    }

    private function sendSms($user_id, $event)
    {
        /**@var CaptainEvent $event * */
        try {
            $smsConfig = CommonAppConfig::getSmsConfig(0);
            if (!isset($smsConfig['bonus']) || !isset($smsConfig['bonus']['template_id']) || !isset($smsConfig['bonus']['name'])) {
                throw new \Exception('团队分红短信未设置正确');
            }
            $data[$smsConfig['bonus']['name']] = $event->captain->user->nickname;
            $message = new Message([
                'template' => $smsConfig['bonus']['template_id'],
                'data' => $data
            ]);
            $captain = BonusCaptain::findOne(['user_id' => $user_id, 'is_delete' => 0, 'status' => CommonCaptain::STATUS_BECOME]);
            $captain && \Yii::$app->sms->module('mall')->send($captain['mobile'], $message);
        } catch (\Exception $exception) {
            \Yii::error('=====团队分红短信通知失败=====');
            \Yii::error($exception);
        }
    }
}