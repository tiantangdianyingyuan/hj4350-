<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order\rprint;

class NullPrint extends BaseForm
{
    public function track(...$params)
    {
        throw new \Exception('未知错误');
    }
}
