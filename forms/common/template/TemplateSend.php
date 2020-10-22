<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/20
 * Time: 18:24
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template;


use app\jobs\TemplateSendJob;
use app\models\Model;
use app\models\User;
use app\models\UserInfo;

class TemplateSend extends Model
{
    public $user;
    public $page;
    public $data;
    public $templateTpl;
    public $templateId;
    public $titleStyle;
    public $platform;
    public $dataKey;

    /* @var TemplateSender */
    public $sender;

    /**
     * @return array
     * @throws \Exception
     */
    public function sendTemplate()
    {
        if (!is_array($this->user)) {
            if (isset($this->user) && $this->user instanceof User) {
                $this->platform = $this->user->userInfo->platform;
            } else {
                throw new \Exception('参数错误，缺少有效的参数user');
            }
        } else {
            $this->platform = $this->user[0]->userInfo->platform;
        }
        $token = \Yii::$app->security->generateRandomString(32);
        $templateMsg['page'] = $this->page;
        $templateMsg['data'] = $this->data;
        $templateMsg['templateTpl'] = $this->templateTpl;
        $templateMsg['templateId'] = $this->templateId;
        $templateMsg['user'] = $this->user;
        $templateMsg['token'] = $token;
        $templateMsg['dataKey'] = $this->dataKey;
        $templateMsg['titleStyle'] = $this->titleStyle;
        $queueId = \Yii::$app->queue->delay(0)->push(new TemplateSendJob($templateMsg));
        return [
            'queueId' => $queueId,
            'token' => $token
        ];
    }
}
