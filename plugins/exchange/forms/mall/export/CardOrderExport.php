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
use app\plugins\exchange\models\ExchangeGoods;

class CardOrderExport extends BaseExport
{
    public $goods_id;

    public $page;

    public function fieldsList()
    {
        return [
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '用户名',
            ],
            [
                'key' => 'platform',
                'value' => '兑换渠道',
            ],
            [
                'key' => 'goods_name',
                'value' => '礼品卡',
            ],
            [
                'key' => 'library_name',
                'value' => '礼品码库',
            ],
            [
                'key' => 'code',
                'value' => '兑换码',
            ],
            [
                'key' => 'created_at',
                'value' => '购买时间',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ]
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
        $g = ExchangeGoods::find()->where([
            'goods_id' => $this->goods_id
        ])->one();
        if ($g) {
            return sprintf('%s-订单记录导出', $g->library->name);
        }
        return '礼品卡-订单记录导出';
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            CommonModel::getStatus($item->library, $item->code, $msg);
            $newList[] = [
                'order_no' => $item->order->order_no,
                'avatar' => $item->user->userInfo->avatar,
                'user_id' => $item->user->id,
                'nickname' => $item->user->nickname,
                'platform' => CommonModel::getPlatform($item->user->userInfo->platform),
                'goods_name' => $item->goods->name,
                'library_name' => $item->library->name,
                'code' => $item->code->code,
                'created_at' => $item->created_at,
                'status' => $msg,
            ];
        }
        $this->dataList = $newList;
    }
}
