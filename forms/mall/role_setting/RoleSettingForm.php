<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\role_setting;


use app\core\response\ApiCode;
use app\models\Model;

class RoleSettingForm extends Model
{
    public function getDetail()
    {
        $form = new \app\forms\common\RoleSettingForm();
        $setting = $form->getSetting();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
