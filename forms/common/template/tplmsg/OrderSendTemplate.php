<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 15:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class OrderSendTemplate
 * @package app\forms\common\template\tplmsg
 * 订单发货通知
 */
class OrderSendTemplate extends BaseTemplate
{
    protected $templateTpl = 'order_send_tpl';
    public $name; // 商品名称
    public $express; // 快递公司
    public $express_no; // 快递单号
    public $remark; // 备注

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->name,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->express,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->express_no,
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
        $this->name = '测试商品';
        $this->express = '测试快递';
        $this->express_no = '986753421';
        $this->remark = '您的订单已发货';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
