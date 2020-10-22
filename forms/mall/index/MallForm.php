<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\index;


use app\core\response\ApiCode;
use app\forms\mall\recharge\RechargeSettingForm;
use app\models\Mall;
use app\models\Model;

class MallForm extends Model
{
    public function getDetail()
    {
        $mall = new Mall();
        $mall = $mall->getMallSetting();
        $rechargeForm = new RechargeSettingForm();
        $setting = $rechargeForm->setting();
        $mall['recharge'] = $setting;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $mall
            ],
        ];
    }
}
