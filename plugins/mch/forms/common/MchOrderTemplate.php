<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 17:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mch\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\mch\forms\mall\TemplateForm;

/**
 * Class MchOrderTemplate
 * @package app\plugins\mch\forms\common
 * 多商户新订单通知
 */
class MchOrderTemplate extends BaseTemplate
{
    protected $templateTpl = 'mch_order_tpl';
    public $order_no;
    public $price;
    public $time;
    public $remark;

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->order_no,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->time,
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
        $this->order_no = 'E20191209174210012000001';
        $this->price = '120元';
        $this->time = '12019/10/10';
        $this->remark = '新订单通知';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
