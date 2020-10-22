<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\booking\forms\mall;


class OrderExport extends \app\forms\mall\export\OrderExport
{
    public $send_type;

    public function fieldsList()
    {
        $exportFields = parent::fieldsList();
        foreach ($exportFields as $key =>  $item) {
            if ($item['key'] == 'city_name' || $item['key'] == 'city_mobile') {
                unset($exportFields[$key]);
            }
        }
        return array_values($exportFields);
    }

    public function getFileName()
    {
        $name = '预约-自提订单';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }
}
