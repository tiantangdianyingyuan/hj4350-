<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 15:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class OrderPayTemplate
 * @package app\forms\common\template\tplmsg
 * 下单成功通知
 */
class OrderPayTemplate extends BaseTemplate
{
    protected $templateTpl = 'order_pay_tpl';
    public $order_no; // 订单编号
    public $pay_time; // 下单时间
    public $price; // 订单金额
    public $goodsName; // 商品名称

    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->order_no,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->pay_time,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword4' => [
                'value' => $this->goodsName,
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    public function test()
    {
        $this->order_no = 'ADWMP2933887762';
        $this->pay_time = '2019-10-14 27:34:21';
        $this->price = '3233.33元';
        $this->goodsName = '测试商品';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
