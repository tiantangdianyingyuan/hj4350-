<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 13:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\dianqilai\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\dianqilai\forms\TemplateForm;

class CommonTemplate extends BaseTemplate
{
    protected $templateTpl = 'contact_tpl';
    public $serviceName;
    public $timestamp;
    public $content;

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->serviceName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => date('Y-m-d H:i:s', $this->timestamp),
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => "【消息内容】" . urldecode($this->content),
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    public function test()
    {
        $this->serviceName = '回复者';
        $this->timestamp = time();
        $this->content = '回复消息';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
