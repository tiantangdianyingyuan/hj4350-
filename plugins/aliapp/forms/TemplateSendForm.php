<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/20
 * Time: 14:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\aliapp\forms;


use Alipay\AlipayRequestFactory;
use app\forms\common\template\TemplateSender;
use app\plugins\aliapp\models\AliappTemplate;
use app\plugins\aliapp\Plugin;

class TemplateSendForm extends TemplateSender
{
    /**
     * @param array $arg
     * @return array
     * @throws \Exception
     */
    public function sendTemplate($arg = array())
    {
        $plugin = new Plugin();
        $arg['data'] = $plugin->getTemplateData($arg['templateTpl'], $arg['data']);
        $aop = $plugin->getAliAopClient();
        if (isset($arg['templateId']) && $arg['templateId']) {
            $templateId = $arg['templateId'];
        } else {
            if (!isset($arg['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $aliappTemplate = AliappTemplate::findOne([
                'tpl_name' => $arg['templateTpl'],
                'mall_id' => $arg['user']->mall_id,
            ]);
            if ($aliappTemplate) {
                $templateId = $aliappTemplate->tpl_id;
            } else {
                throw new \Exception('模板消息尚未配置。');
            }
        }
        $request = AlipayRequestFactory::create('alipay.open.app.mini.templatemessage.send', [
            'biz_content' => [
                'to_user_id' => $arg['user']->userInfo->platform_user_id,
                'form_id' => $arg['formId'],
                'user_template_id' => $templateId,
                'page' => '/pages/index/index',
                'data' => $arg['data'],
            ],
        ]);
        $response = $aop->execute($request);
        if ($response->isSuccess() === false) {
            \Yii::error($response->getError());
            throw new \Exception("模板消息发送失败：response=>" . json_encode($response->getError(), JSON_UNESCAPED_UNICODE));
        }
        return $response->getData();
    }
}
