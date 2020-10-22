<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonSetting;

class SettingForm extends Model
{
    public function get()
    {
        $commonSetting = new CommonSetting();
        $setting = $commonSetting->get();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $setting,
        ];
    }
}