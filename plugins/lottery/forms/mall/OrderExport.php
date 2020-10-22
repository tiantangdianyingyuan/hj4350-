<?php

namespace app\plugins\lottery\forms\mall;


class OrderExport extends \app\forms\mall\export\OrderExport
{
    public $send_type;

    public function getFileName()
    {
        $name = $this->send_type == 1 ? '抽奖-自提订单' : '抽奖-订单列表';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }
}
