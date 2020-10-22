<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/5
 * Time: 18:45
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class ActivitySuccessTemplate
 * @package app\forms\common\template\tplmsg
 * 活动状态通知
 */
class ActivitySuccessTemplate extends BaseTemplate
{
    protected $templateTpl = 'enroll_success_tpl';
    public $activityName;
    public $name;
    public $remark;

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->activityName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->name,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->activityName = '双十一活动';
        $this->name = '即将结束';
        $this->remark = '请及时领取';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
