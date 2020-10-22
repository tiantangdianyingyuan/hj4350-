<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\step\forms\mall;

use app\forms\mall\export\OrderExport;

class Export extends OrderExport
{
    public function getFileName()
    {
        $name = '步数宝-订单列表';
        $fileName = $name . date('YmdHis');
        return $fileName;
    }
}
