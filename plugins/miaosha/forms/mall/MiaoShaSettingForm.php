<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\miaosha\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\models\Model;
use app\plugins\miaosha\forms\common\v2\SettingForm;
use app\plugins\miaosha\forms\common\v2\CommonOption;

class MiaoShaSettingForm extends Model
{
    public function getSetting()
    {
        $setting = (new SettingForm())->search();
        $setting['goods_poster'] = (new CommonOptionP())->poster($setting['goods_poster'], CommonOption::getPosterDefault());
        $setting['goods_poster']['price']['text'] = CommonOption::getPosterDefault()['price']['text'];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $setting,
                'time_list' => $this->options(),
            ]
        ];
    }

    public function options()
    {
        return [
            [
                'label' => '00:00~00:59',
                'value' => 0,
            ],
            [
                'label' => '01:00~01:59',
                'value' => 1,
            ],
            [
                'label' => '02:00~02:59',
                'value' => 2,
            ],
            [
                'label' => '03:00~03:59',
                'value' => 3,
            ],
            [
                'label' => '04:00~04:59',
                'value' => 4,
            ],
            [
                'label' => '05:00~05:59',
                'value' => 5,
            ],
            [
                'label' => '06:00~06:59',
                'value' => 6,
            ],
            [
                'label' => '07:00~07:59',
                'value' => 7,
            ],
            [
                'label' => '08:00~08:59',
                'value' => 8,
            ],
            [
                'label' => '09:00~09:59',
                'value' => 9,
            ],
            [
                'label' => '10:00~10:59',
                'value' => 10,
            ],
            [
                'label' => '11:00~11:59',
                'value' => 11,
            ],
            [
                'label' => '12:00~12:59',
                'value' => 12,
            ],
            [
                'label' => '13:00~13:59',
                'value' => 13,
            ],
            [
                'label' => '14:00~14:59',
                'value' => 14,
            ],
            [
                'label' => '15:00~15:59',
                'value' => 15,
            ],
            [
                'label' => '16:00~16:59',
                'value' => 16,
            ],
            [
                'label' => '17:00~17:59',
                'value' => 17,
            ],
            [
                'label' => '18:00~18:59',
                'value' => 18,
            ],
            [
                'label' => '19:00~19:59',
                'value' => 19,
            ],
            [
                'label' => '20:00~20:59',
                'value' => 20,
            ],
            [
                'label' => '21:00~21:59',
                'value' => 21,
            ],
            [
                'label' => '22:00~22:59',
                'value' => 22,
            ],
            [
                'label' => '23:00~23:59',
                'value' => 23,
            ],
        ];
    }
}
