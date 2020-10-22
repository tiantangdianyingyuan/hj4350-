<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/24
 * Time: 17:20
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\gift\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\gift\forms\mall\TemplateForm;

/**
 * Class GiftToUserTemplate
 * @package app\plugins\gift\forms\common
 * 礼物领取人未填地址，通知收礼人
 */
class GiftToUserTemplate extends BaseTemplate
{
    protected $templateTpl = 'gift_to_user';
    public $order_no;
    public $name;
    public $time;
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
        $this->order_no = 'c34876';
        $this->name = '商品信息';
        $this->time = '2019-12-24';
        $this->remark = '请及时领取礼物';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
