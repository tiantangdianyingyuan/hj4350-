<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/3
 * Time: 11:08
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\check_in\forms\mall\TemplateForm;

/**
 * Class CommonTemplate
 * @package app\plugins\check_in\forms\common
 * 签到提醒
 */
class CommonTemplate extends BaseTemplate
{

    protected $templateTpl = 'check_in_tpl';
    /**
     * @return array
     * @throws \Exception
     * 每日签到提醒
     */
    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => '每日签到',
                'color' => '#333333'
            ],
            'keyword2' => [
                'value' => date('Y.m.d', time()) . ' 23:59:59',
                'color' => '#333333'
            ],
            'keyword3' => [
                'value' => '亲，您今天还没有签到哦',
                'color' => '#333333'
            ]
        ];
        return $data;
    }

    public function test()
    {
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
