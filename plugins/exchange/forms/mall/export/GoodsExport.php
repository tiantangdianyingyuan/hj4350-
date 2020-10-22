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

class GoodsExport extends BaseExport
{
    public $send_type;

    public $page;

    public function fieldsList()
    {
        return [
            [
                'key' => 'id',
                'value' => 'id',
            ],
            [
                'key' => 'name',
                'value' => '礼品卡名称',
            ],
            [
                'key' => 'price',
                'value' => '价格',
            ],
            [
                'key' => 'library_name',
                'value' => '兑换码库',
            ],
            [
                'key' => 'goods_stock',
                'value' => '库存',
            ],
            [
                'key' => 'sales',
                'value' => '已出售量',
            ],
            [
                'key' => 'created_at',
                'value' => '添加时间',
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
            $list = $query->page($pagination, 50, $this->page)->all();
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
        return '礼品卡-导出';
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['name'] = $item->name;
            $arr['price'] = $item->price;
            $arr['library_name'] = $item->library->name;
            $arr['goods_stock'] = $item->goods_stock;
            $arr['sales'] = intval($item->sales) + intval($item->virtual_sales);
            $arr['created_at'] = $item->created_at;
            $arr['status'] = $item->status == 1 ? '销售中' : '下架';
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
