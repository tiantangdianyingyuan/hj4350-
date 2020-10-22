<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\copyright;


use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class CopyrightForm extends Model
{
    public function getDetail()
    {
        $option = CommonAppConfig::getCoryRight();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
            ]
        ];
    }

    public function getDefault()
    {
        return [
            'pic_url' => '',
            'description' => '',
            'type' => '1',
            'link_url' => '',
            'mobile' => '',
            'link' => '',
            'status' => '1'
        ];
    }
}
