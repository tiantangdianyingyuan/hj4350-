<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\api\goods;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonHotSearch;
use app\models\Model;

class HotSearchForm extends Model
{

    public function getList()
    {
        $common = new CommonHotSearch();
        $data = $common->getAll();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => [
                'list' => $data
            ],
        ];
    }
}
