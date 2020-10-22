<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 17:09
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\gift\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\gift\forms\mall\TemplateForm;

/**
 * Class GiftConvertTemplate
 * @package app\plugins\gift\forms\common
 * 中奖结果通知
 */
class GiftConvertTemplate extends BaseTemplate
{
    protected $templateTpl = 'gift_convert';
    public $title;
    public $name;
    public $result = '可喜可贺';
    public $remark;

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->title,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->name,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->result,
                'color' => '#333333',
            ],
            'keyword4' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    public function test()
    {
        $this->title = '现场摇一摇';
        $this->name = '手机';
        $this->result = '中奖';
        $this->remark = '尽快来领取属于你的专属礼品！';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
