<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\diy\forms\mall;

use app\forms\mall\export\BaseExport;
use app\plugins\diy\models\DiyForm;

class InfoExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'created_at',
                'value' => '添加时间',
            ],
            [
                'key' => 'form_data',
                'value' => '表单信息',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy('created_at DESC')->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        (new CsvExport())->export($dataList, $this->fieldsNameList, $this->getFileName());
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $fileName = 'diy表单' . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        /** @var DiyForm $item */
        foreach ($list as $item) {

            $formData = json_decode($item->form_data, true);
            // 新增图片上传数据格式转换
            if (is_array($formData) || $formData instanceof \ArrayObject) {
                foreach ($formData as &$_item) {
                    if (!isset($_item['key']) || $_item['key'] !== 'img_upload') {
                        continue;
                    }
                    if (!isset($_item['value'])) {
                        continue;
                    }
                    if (is_string($_item['value'])) {
                        $_item['value'] = [$_item['value']];
                    }
                }
            }

            $arr = [];
            $arr['number'] = $number++;
            $arr['id'] = $item->id;
            $arr['nickname'] = $item->user->nickname;
            $arr['created_at'] = $item->created_at;
            $arr['form_data'] = $formData;
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
