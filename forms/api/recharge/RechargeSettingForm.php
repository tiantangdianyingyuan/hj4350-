<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\recharge;

use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\models\Model;

class RechargeSettingForm extends Model
{
    public function rules()
    {
        return [];
    }

    public function getIndex()
    {
        $setting = CommonAppConfig::getRechargeSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting
            ]
        ];
    }
}
