<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/12
 * Time: 9:48
 */

namespace app\plugins\ttapp\forms;


use app\forms\common\template\TemplateSender;
use app\plugins\ttapp\models\TtappTemplate;
use app\plugins\ttapp\models\TtTemplate;
use app\plugins\ttapp\Plugin;


class TemplateSendForm extends TemplateSender
{
    /**
     * @param array $arg
     * @return array
     * @throws \Exception
     * 头条小程序发送模板消息
     */
    public function sendTemplate($arg = array())
    {
        $plugin = new Plugin();
        $arg['data'] = $plugin->getTemplateData($arg['templateTpl'], $arg['data']);
        $accessToken = $plugin->getAccessToken();
        $template = new TtTemplate([
            'accessToken' => $accessToken
        ]);
        if (isset($arg['templateId']) && $arg['templateId']) {
            $templateId = $arg['templateId'];
        } else {
            if (!isset($arg['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $ttappTemplate = TtappTemplate::findOne([
                'tpl_name' => $arg['templateTpl'],
                'mall_id' => $arg['user']->mall_id,
            ]);
            if ($ttappTemplate) {
                $templateId = $ttappTemplate->tpl_id;
            } else {
                throw new \Exception('模板消息尚未配置。');
            }
        }
        $res = $template->sendTemplateMessage([
            'touser' => $arg['user']->userInfo->platform_user_id,
            'form_id' => $arg['formId'],
            'template_id' => $templateId,
            'page' => $arg['page'],
            'data' => $arg['data'],
        ]);
        return $res;
    }
}
