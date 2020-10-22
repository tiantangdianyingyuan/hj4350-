<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/5
 * Time: 16:29
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class ShareAudiTemplate
 * @package app\forms\common\template\tplmsg
 * 审核结果通知
 */
class ShareAudiTemplate extends BaseTemplate
{
    public $reviewProject; // 审核项目
    public $result; // 审核结果
    public $nickname; // 分销商昵称
    public $time; // 审核时间
    protected $templateTpl = 'share_audit_tpl';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->reviewProject,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->result,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->nickname,
                'color' => '#333333',
            ],
            'keyword4' => [
                'value' => $this->time,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->reviewProject = '恭喜您，您提交的申请已通过审核';
        $this->result = '通过';
        $this->nickname = '测试店铺';
        $this->time = '2019年10月10日 10:10';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
