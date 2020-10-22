<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mch;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;

class CashLogExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'money',
                'value' => '提现金额',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'status',
                'value' => '提现状态',
            ],
            [
                'key' => 'type',
                'value' => '提现类型',
            ],
            [
                'key' => 'created_at',
                'value' => '提现日期',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy('created_at')->asArray()->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '提现记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['money'] = (float)$item['money'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['order_no'] = $item['order_no'];
            switch ($item['status']) {
                case 0:
                    $arr['status'] = '待处理';
                    break;
                case 1:
                    $arr['status'] = '已转账';
                    break;
                case 2:
                    $arr['status'] = '已拒绝';
                    break;
                default:
                    $arr['status'] = '未知';
                    break;
            }
            switch ($item['type']) {
                case 'wx':
                    $arr['type'] = '微信转账';
                    break;
                case 'alipay':
                    $arr['type'] = '支付宝转账';
                    break;
                case 'bank':
                    $arr['type'] = '银行卡转账';
                    break;
                case 'balance':
                    $arr['type'] = '转账到余额';
                    break;
                case 'auto':
                    $arr['type'] = '平台自动转账';
                    break;
                default:
                    $arr['type'] = '未知';
                    break;
            }
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
