<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 17:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\lottery\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\lottery\forms\mall\TemplateForm;

/**
 * Class LotteryTemplate
 * @package app\plugins\lottery\forms\common
 * 中奖结果通知
 */
class LotteryTemplate extends BaseTemplate
{
    protected $templateTpl = 'lottery_tpl';
    public $activityName;
    public $goodsName;
    public $result;
    public $remark;

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->activityName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->goodsName,
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
    }

    public function test()
    {
        $this->activityName = '现场摇一摇';
        $this->goodsName = '手机';
        $this->result = '中奖';
        $this->remark = '尽快来领取属于你的专属礼品！';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
