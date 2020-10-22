<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class PriceStatisticsExport extends BaseExport
{

    public $start_time;
    public $end_time;
    public $platform;

    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_price',
                'value' => '订单收益（元）',
            ],
            [
                'key' => 'member_price',
                'value' => '会员购买收益（元）',
            ],
            [
                'key' => 'balance',
                'value' => '余额充值收益（元）',
            ],
            [
                'key' => 'cash_price',
                'value' => '提现支出（元）',
            ],
            [
                'key' => 'income_price',
                'value' => '实际收益（元）',
            ],
        ];
    }

    public function export($list)
    {
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '对账单' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($item)
    {
        $newList = [];
        $arr = [];
        $item['date'] = empty($this->start_time) ? '全部' : $this->start_time . '-' . $this->end_time;
        $item['order_price'] = floatval($item['order_price']);
        $item['member_price'] = floatval($item['member_price']);
        $item['balance'] = floatval($item['balance']);
        $item['cash_price'] = floatval(0 - $item['cash_price']);
        $item['income_price'] = floatval($item['income_price']);
        $item['platform'] = $this->getPlatform($this->platform);
        $item['platform'] = $item['platform'] == '未知' ? '全部' : $item['platform'];

        $arr = array_merge($arr, $item);
        $newList[] = $arr;
        $this->dataList = $newList;
    }

    protected function getFields()
    {
        $arr = [];
        foreach ($this->fieldsList() as $key => $item) {
            $arr[$key] = $item['key'];
        }
        $this->fieldsKeyList = $arr;
        $fieldsList = $this->fieldsList();
        $newFields = ['日期'];
        if ($this->fieldsKeyList) {
            foreach ($this->fieldsKeyList as $field) {
                foreach ($fieldsList as $item) {
                    if ($item['key'] === $field) {
                        $newFields[] = $item['value'];
                    }
                }
            }
        }
        $this->fieldsKeyList = array_merge(['date'], $this->fieldsKeyList ?: []);
        $this->fieldsNameList = $newFields;
    }
}
