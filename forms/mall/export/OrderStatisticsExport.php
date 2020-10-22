<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class OrderStatisticsExport extends BaseExport
{

    public $name;

    public function fieldsList()
    {
        return [
            [
                'key' => 'time',
                'value' => '日期',
            ],
            [
                'key' => 'user_num',
                'value' => '付款人数',
            ],
            [
                'key' => 'order_num',
                'value' => '付款订单数',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '付款金额',
            ],
            [
                'key' => 'goods_num',
                'value' => '付款件数',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query
            ->asArray()
            ->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $ex_name = !empty($this->name) ? $this->name . '-' : '';
        $fileName = $ex_name . '销售统计' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        $number = 1;
        foreach ($list as $key => $item) {
            $arr['number'] = $number++;
            $item['user_num'] = intval($item['user_num']);
            $item['order_num'] = intval($item['order_num']);
            $item['total_pay_price'] = floatval($item['total_pay_price']);
            $item['goods_num'] = intval($item['goods_num']);
            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }

    protected function getFields()
    {
        $arr = [];
        foreach ($this->fieldsList() as $key => $item) {
            $arr[$key] = $item['key'];
        }
        $this->fieldsKeyList = $arr;
        parent::getFields(); // TODO: Change the autogenerated stub
    }
}
