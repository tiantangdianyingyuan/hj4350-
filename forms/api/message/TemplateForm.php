<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/25
 * Time: 14:13
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\message;


use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\forms\common\template\tplmsg\AccountChange;
use app\forms\common\template\tplmsg\ActivityRefundTemplate;
use app\forms\common\template\tplmsg\ActivitySuccessTemplate;
use app\forms\common\template\tplmsg\AudiResultTemplate;
use app\forms\common\template\tplmsg\BaseTemplate;
use app\forms\common\template\tplmsg\OrderCancelTemplate;
use app\forms\common\template\tplmsg\OrderPayTemplate;
use app\forms\common\template\tplmsg\OrderRefund;
use app\forms\common\template\tplmsg\OrderSendTemplate;
use app\forms\common\template\tplmsg\RemoveIdentityTemplate;
use app\forms\common\template\tplmsg\ShareAudiTemplate;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\Model;
use app\models\TemplateRecord;

class TemplateForm extends Model
{
    public $templateTpl;

    public function rules()
    {
        return [
            [['templateTpl'], 'trim'],
            [['templateTpl'], 'string'],
        ];
    }

    public function getList()
    {
        try {
            $platform = \Yii::$app->appPlatform;
            $list = TemplateList::getInstance()->getTemplateList($platform);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '平台不支持模板消息'
            ];
        }
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $templateList = $this->templateList();
        $pluginList = \Yii::$app->plugin->getList();
        foreach ($pluginList as $name) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($name->name);
                $list = $plugin->templateList();
                if (is_array($list) && !empty($list)) {
                    $templateList = array_merge($templateList, $list);
                }
            } catch (\Exception $exception) {
                \Yii::error($exception);
            }
        }
        $data = [];
        if (isset($templateList[$this->templateTpl])) {
            $class = $templateList[$this->templateTpl];
            $template = new $class([
                'user' => \Yii::$app->user->identity,
            ]);
            if ($template instanceof BaseTemplate) {
                $res = $template->test();
                $isDone = true;

                while ($isDone) {
                    if (\Yii::$app->queue->isDone($res['queueId'])) {
                        $templateRecord = TemplateRecord::findOne(['token' => $res['token']]);
                        $data = [
                            'status' => $templateRecord->status,
                            'msg' => $templateRecord->status == 1 ? '发送成功' : $templateRecord->error,
                        ];
                        $isDone = false;
                    }
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $data
            ];
        }
        return [
            'code' => ApiCode::CODE_ERROR,
            'msg' => '发送失败'
        ];
    }

    protected function templateList()
    {
        return [
            'account_change_tpl' => AccountChange::class,
            'enroll_error_tpl' => ActivityRefundTemplate::class,
            'enroll_success_tpl' => ActivitySuccessTemplate::class,
            'order_cancel_tpl' => OrderCancelTemplate::class,
            'order_pay_tpl' => OrderPayTemplate::class,
            'order_refund_tpl' => OrderRefund::class,
            'order_send_tpl' => OrderSendTemplate::class,
            'share_audit_tpl' => ShareAudiTemplate::class,
            'withdraw_error_tpl' => WithdrawErrorTemplate::class,
            'withdraw_success_tpl' => WithdrawSuccessTemplate::class,
            'audit_result_tpl' => AudiResultTemplate::class,
            'remove_identity_tpl' => RemoveIdentityTemplate::class,
        ];
    }
}
