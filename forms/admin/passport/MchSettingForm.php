<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\passport;

use app\core\response\ApiCode;
use app\models\Model;


class MchSettingForm extends Model
{
    public $mall_id;

    public function rules()
    {
        return [
            [['mall_id'], 'integer'],
        ];
    }

    public function getMchSetting()
    {
        $form = new \app\forms\common\mch\MchSettingForm();
        $res = $form->search(base64_decode($this->mall_id));
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $res,
            ]
        ];
    }
}
