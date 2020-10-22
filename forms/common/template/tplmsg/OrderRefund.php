<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 15:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class OrderRefund
 * @package app\forms\common\template\tplmsg
 * 退款通知
 */
class OrderRefund extends BaseTemplate
{
    protected $templateTpl = 'order_refund_tpl';
    public $order_no;
    public $name;
    public $refundPrice;
    public $remark;

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->order_no,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->name,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->refundPrice,
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
        $this->order_no = 'YM2019110712121200001';
        $this->name = '测试商品';
        $this->refundPrice = '2元';
        $this->remark = '下单失败';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
