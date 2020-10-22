<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;

class ClerkOrderExport extends BaseExport
{
    public $send_type;

    public $page;

    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'clerk_user_name',
                'value' => '核销员',
            ],
            [
                'key' => 'clerk_store_name',
                'value' => '核销门店',
            ],
            [
                'key' => 'clerk_time',
                'value' => '核销时间',
            ]
        ];

        return $fieldsList;
    }

    public function export($query)
    {
        $query->with('store', 'clerkUser.user.userInfo')
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        try {

            $filePath = \Yii::$app->basePath . '/web/temp/goods/' . $this->getFileName() . '.csv';

            if ($this->page == 1 && file_exists($filePath)) {
                unlink($filePath);
            }

            $list = $query->page($pagination, 50, $this->page)->all();

            $this->transform($list);
            $this->getFields();
            $dataList = $this->getDataList();
            (new CsvExport())->ajaxExport($dataList, $this->fieldsNameList, $this->getFileName());

            if ($this->page > $pagination->page_count) {
                $download_url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/goods/' .$this->getFileName() . '.csv?time=' . time();
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
        $fileName = '核销订单' . \Yii::$app->mall->id;

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item->clerkUser->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['clerk_user_name'] = $item->clerkUser->user->nickname;
            $arr['clerk_store_name'] = $item->store->name;
            $arr['clerk_time'] = $item->send_time;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    protected function getIsAddNumber()
    {
        return false;
    }
}
