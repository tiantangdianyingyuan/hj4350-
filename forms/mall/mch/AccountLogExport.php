<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\mch;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;

class AccountLogExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'money',
                'value' => '金额',
            ],
            [
                'key' => 'desc',
                'value' => '说明',
            ],
            [
                'key' => 'type',
                'value' => '收支类型',
            ],
            [
                'key' => 'created_at',
                'value' => '收支日期',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy('created_at')->asArray()->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '收支记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['desc'] = $item['desc'];
            $arr['money'] = (float)$item['money'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['type'] = $item['type'] == 1 ? '收入' : '支出';
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
