<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 16:43
 */

namespace app\plugins\advance\forms\mall;


class OrderExport extends \app\forms\mall\export\OrderExport
{
    public function getFileName()
    {
        $name = '预售订单-订单列表';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }
}
