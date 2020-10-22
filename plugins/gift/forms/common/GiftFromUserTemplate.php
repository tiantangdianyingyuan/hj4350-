<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 17:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\gift\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\gift\forms\mall\TemplateForm;

/**
 * Class GiftFromUserTemplate
 * @package app\plugins\gift\forms\common
 * 礼物领取人未填地址，通知送礼人
 */
class GiftFromUserTemplate extends BaseTemplate
{
    protected $templateTpl = 'gift_form_user';
    public $order_no;
    public $name;
    public $remark = '礼物超时失效';

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
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    public function test()
    {
        $this->order_no = 'c34876';
        $this->name = '待确认收货';
        $this->remark = '请确认商品数量及包装是否完好';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
