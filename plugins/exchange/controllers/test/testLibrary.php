<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

use PHPUnit\Framework\TestCase;

class testSetting extends TestCase
{
    public function test()
    {
        //https://localhost/zjhj_mall_v4/web/index.php?r=plugin%2Fexchange%2Fmall%2Flibrary/list

        $a = \Yii::$app->request;
        dd($a);
        echo 1;
    }
}