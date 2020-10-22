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

class RecordLogExport extends BaseExport
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
                'key' => 'platform',
                'value' => '兑换渠道',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '兑换会员',
            ],
            [
                'key' => 'rewards_text',
                'value' => '兑换奖励',
            ],
            [
                'key' => 'r_raffled_at',
                'value' => '兑换时间',
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
        $library = CommonModel::getLibrary($this->library_id);
        $library && $name = $library->name;
        return sprintf('%s-兑换记录', $name ?? '');
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['code'] = $item->code;
            $arr['platform'] = CommonModel::getPlatform($item->user->userInfo->platform);
            $arr['user_id'] = $item->user->userInfo->user_id ?? '';
            $arr['nickname'] = $item->user->nickname ?? '';
            $rewards = \yii\helpers\BaseJson::decode($item['r_rewards']);
            $arr['rewards_text'] =  implode(',', array_unique(array_map(function ($reward) {
                return $reward['name'];
            }, $rewards)));
            $arr['r_raffled_at'] = $item->r_raffled_at;
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
