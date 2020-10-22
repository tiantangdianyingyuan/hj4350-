<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 10:14
 */

namespace app\plugins\advance\models;

use app\forms\common\template\tplmsg\BaseTemplate;

/**
 * Class TailMoneyTemplate
 * @package app\plugins\advance\models
 * 尾款支付通知
 */
class TailMoneyTemplate extends BaseTemplate
{
    public $price; // 尾款金额
    public $goodsName; // 商品名称
    protected $templateTpl = 'pay_advance_balance';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->goodsName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => "错过支付尾款订单会被取消，定金不退哦",
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->goodsName = 'xx联名款秋冬长裙';
        $this->price = '10元';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
