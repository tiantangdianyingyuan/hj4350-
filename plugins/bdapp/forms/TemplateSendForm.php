<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/12
 * Time: 9:48
 */

namespace app\plugins\bdapp\forms;


use app\forms\common\template\TemplateSender;
use app\plugins\bdapp\models\BdappTemplate;
use app\plugins\bdapp\models\BdTemplate;
use app\plugins\bdapp\Plugin;


class TemplateSendForm extends TemplateSender
{
    private $mallId;
    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * 百度小程序发送模板消息
     */
    public function sendTemplate($data = array())
    {
        $plugin = new Plugin();
        $this->mallId = $data['user']->mall_id;
        $accessToken = $plugin->getAccessToken();
        $template = new BdTemplate([
            'accessToken' => $accessToken
        ]);
        if (isset($data['templateId']) && $data['templateId']) {
            $templateId = $data['templateId'];
        } else {
            if (!isset($data['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $bdappTemplate = BdappTemplate::findOne([
                'tpl_name' => $data['templateTpl'],
                'mall_id' => $this->mallId,
            ]);
            if ($bdappTemplate) {
                $templateId = $bdappTemplate->tpl_id;
            } else {
                throw new \Exception('模板消息尚未配置。');
            }
        }
        $res = $template->sendTemplateMessage([
            'touser_openId' => $data['user']->userInfo->platform_user_id,
            'scene_id' => $data['formId'],
            'scene_type' => 1,
            'template_id' => $templateId,
            'page' => $data['page'],
            'data' => json_encode($data['data']),
        ]);
        return $res;
    }
}