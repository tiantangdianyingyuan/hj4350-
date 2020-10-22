<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/1/15
 * Time: 9:41
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class ShareAudiTemplate
 * @package app\forms\common\template\tplmsg
 * 审核结果通知
 */
class RemoveIdentityTemplate extends BaseTemplate
{
    public $remark; // 备注说明
    public $time; // 变更时间
    protected $templateTpl = 'remove_identity_tpl';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->time,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->remark = '业绩不达标，你的分销商身份已被解除';
        $this->time = '2019年10月10日 10:10';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
