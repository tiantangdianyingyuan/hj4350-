<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/23
 * Time: 15:17
 */

namespace app\plugins\ttapp\forms;

class TemplateInfo
{
    private $data;

    public function __construct($type, $info)
    {
        foreach ($info as $k => $v) {
            unset($info[$k]['color']);
        }

        switch ($type) {
            case "order_pay_tpl":
                $this->data =  [
                    'keyword1' => [
                        'value' => $info['keyword1']['value'],
                    ],
                    'keyword2' => [
                        'value' => $info['keyword2']['value'],
                    ],
                    'keyword3' => [
                        'value' => $info['keyword4']['value'],
                    ],
                    'keyword4' => [
                        'value' => $info['keyword3']['value'],
                    ],
                ];
                break;

            default:
                $this->data =  $info;
                break;
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
