<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/21
 * Time: 18:17
 */

namespace app\plugins\vip_card\models;

use app\forms\common\template\tplmsg\BaseTemplate;

/**
 * Class RemindTemplate
 * @package app\plugins\vip_card\models
 * 会员卡到期提醒
 */
class RemindTemplate extends BaseTemplate
{
    public $endTime; // 到期日
    public $cardName; // 商品名称
    protected $templateTpl = 'vip_card_remind';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->cardName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->endTime,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->cardName = '月卡';
        $this->endTime = '2020-01-04';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = new TemplateForm();
    }
}
