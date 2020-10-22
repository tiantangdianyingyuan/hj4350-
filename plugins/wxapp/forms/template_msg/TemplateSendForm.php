<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/20
 * Time: 9:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\forms\template_msg;


use app\forms\common\template\TemplateSender;
use app\plugins\wxapp\models\WechatTemplate;
use app\plugins\wxapp\models\WxappTemplate;
use app\plugins\wxapp\Plugin;

class TemplateSendForm extends TemplateSender
{
    private $mallId;

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * 微信发送订阅消息
     */
    public function sendTemplate($data = array())
    {
        $plugin = new Plugin();
        $this->mallId = $data['user']->mall_id;
        $template = $plugin->getWechatTemplate();
        if (isset($data['templateId']) && $data['templateId']) {
            $templateId = $data['templateId'];
        } else {
            if (!isset($data['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $wxappTemplate = WxappTemplate::findOne([
                'tpl_name' => $data['templateTpl'],
                'mall_id' => $this->mallId,
            ]);
            if ($wxappTemplate) {
                $templateId = $wxappTemplate->tpl_id;
            } else {
                $templateId = $this->getTemplateId($plugin, $data);
            }
        }
        $res = $template->sendTemplateMessage([
            'touser' => $data['user']->userInfo->platform_user_id,
            'form_id' => $data['formId'],
            'template_id' => $templateId,
            'page' => $data['page'],
            'data' => $data['data'],
            'emphasis_keyword' => $data['titleStyle']
        ]);
        return $res;
    }

    /**
     * @param Plugin $plugin
     * @param $data
     * @return mixed
     * @throws \Exception
     * 获取template_id
     */
    private function getTemplateId(Plugin $plugin, $data)
    {
        $templateId = '';
        if (!$templateId) {
            $wechatTemplate = $plugin->getWechatTemplate();
            $templateInfoList = $plugin->templateInfoList();
            if (isset($templateInfoList[$data['templateTpl']])) {
                $params = $templateInfoList[$data['templateTpl']];
            } else {
                throw new \Exception('错误的订阅消息参数');
            }
            // 微信小程序平台订阅消息最多可添加数量
            $maxCount = 25;
            // 已查询数量
            $count = 0;
            while (true) {
                $list = $wechatTemplate->getTemplateList($count, 20);
                foreach ($list as $item) {
                    $count++;
                    if ($item['title'] == $params['title']) {
                        $templateId = $item['template_id'];
                        break;
                    }
                }
                if (!(!$templateId && $count == 20 && $count <= $maxCount)) {
                    break;
                }
            }
            if (!$templateId) {
                $res = $wechatTemplate->addTemplate($params['id'], $params['keyword_id_list']);
                $templateId = $res['template_id'];
            }
            if (!$templateId) {
                throw new \Exception('获取template_id错误');
            }
        }
        return $templateId;
    }
}
