<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\common\mptemplate;

use app\forms\common\mptemplate\tplmsg\MpTemplateMsgData;
use app\jobs\MpTplMessageJob;
use app\models\Model;

class MpTplMsgSend extends Model
{
    public $method;
    public $model;
    public $params;

    final public function sendTemplate(MpTplMsgSend $mpModel)
    {
        $this->model = new MpTemplateMsgData();
        if (!method_exists($this->model, $this->method)) {
            throw new \Exception('错误的模板消息发送参数method');
        }

        $token = \Yii::$app->security->generateRandomString(32);

        $templateMsg = $mpModel->getInfo($this);
        $templateMsg['mall'] = \Yii::$app->mall;
        $templateMsg['token'] = $token;
        if(!$templateMsg['app_id']) {
            throw new \Exception('AppID不能为空');
        }
        if(!$templateMsg['app_secret']) {
            throw new \Exception('AppSecret不能为空');
        }
        if(!$templateMsg['admin_open_list']) {
            throw new \Exception('管理员不能为空');
        }
        if(!$templateMsg['templateId']) {
            throw new \Exception('模板不能为空');
        }

        //$a = new MpTplMessageJob($templateMsg);
        //$a->execute(1);
        $queueId = \Yii::$app->queue->delay(0)->push(new MpTplMessageJob($templateMsg));
        return [
            'queueId' => $queueId,
            'token' => $token
        ];
    }
}
