<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\forms\mall\export\BaseExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\exchange\forms\common\CommonModel;

class ExchangeExport extends BaseExport
{
    public $library_id;

    public $page;

    public function fieldsList()
    {
        return [
            [
                'key' => 'code',
                'value' => '兑换码',
            ],
            [
                'key' => 'type',
                'value' => '生成方式',
            ],
            [
                'key' => 'created_at',
                'value' => '生成时间',
            ],
            [
                'key' => 'valid_time',
                'value' => '有效期',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
        ];
    }

    protected function getIsAddNumber()
    {
        return false;
    }

    public function export($query)
    {
        try {
            $this->fieldsKeyList = array_column($this->fieldsList(), 'key');
            $filePath = \Yii::$app->basePath . '/web/temp/goods/' . $this->getFileName() . '.csv';

            if ($this->page == 1 && file_exists($filePath)) {
                @unlink($filePath);
            }
            /** @var BaseActiveQuery $query */
            $list = $query->page($pagination, 2000, $this->page)->all();
            $this->transform($list);

            $this->getFields();
            $dataList = $this->getDataList();
            (new CsvExport())->ajaxExport($dataList, $this->fieldsNameList, $this->getFileName());

            if ($this->page > $pagination->page_count) {
                $download_url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/goods/' . $this->getFileName() . '.csv?time=' . time();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'download_url' => $download_url,
                        'is_finish' => true,
                    ],
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'is_finish' => false,
                    'list' => $list,
                    'pagination' => $pagination,
                ],
            ];

        } catch (\Exception $exception) {
            dd($exception);
        }
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $data = (new CommonModel())->getLibrary($this->library_id);
        return sprintf('%s-兑换码列表导出', $data->name);
    }

    protected function transform($list)
    {
        $newList = [];
        current($list) === false || $library = CommonModel::getLibrary(current($list)['library_id']);
        foreach ($list as $item) {
            $arr = [];
            $arr['code'] = $item->code;
            $arr['type'] = $item['type'] != 1 ? $item['type'] == 0 ? '手动' : '未知' : '礼品卡';
            $arr['valid_time'] = $library['expire_type'] === 'all' ? '永久' : $item['valid_start_time'] . ',' . $item['valid_end_time'];
            (new CommonModel())->getStatus($library, $item, $msg);
            $arr['status'] = $msg;
            $arr['created_at'] = $item['created_at'];
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
