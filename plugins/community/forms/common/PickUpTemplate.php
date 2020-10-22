<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/2
 * Time: 16:15
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\common;


use app\forms\common\template\tplmsg\BaseTemplate;
use app\plugins\community\forms\mall\TemplateForm;

class PickUpTemplate extends BaseTemplate
{
    protected $templateTpl = 'pick_up_tpl';
    public $orderNo;
    public $pickUpNo;
    public $address;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function msg()
    {
        $data = [
            'keyword1' => [
                'value' => $this->orderNo,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->address,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->pickUpNo,
                'color' => '#333333',
            ],
        ];
        return $data;
    }

    /**
     * @return mixed
     * @throws \Exception
     * 测试发送模板消息
     */
    public function test()
    {
        $this->orderNo = '2015231';
        $this->pickUpNo = '#2020-1';
        $this->address = '您的商品已到达团长提货点，请尽快提货';
        return $this->send();
    }

    /**
     * @return mixed
     * 模板消息配置
     */
    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
