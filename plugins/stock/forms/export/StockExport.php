<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 15:00
 */

namespace app\plugins\stock\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\User;
use app\plugins\stock\models\StockUser;
use yii\helpers\ArrayHelper;

class StockExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'name',
                'value' => '姓名',
            ],
            [
                'key' => 'mobile',
                'value' => '手机号',
            ],
            [
                'key' => 'level_name',
                'value' => '股东等级',
            ],
            [
                'key' => 'all_bonus',
                'value' => '累计分红',
            ],
            [
                'key' => 'total_bonus',
                'value' => '可提现分红',
            ],
            [
                'key' => 'applyed_at',
                'value' => '申请时间',
            ],
            [
                'key' => 'agreed_at',
                'value' => '审核时间',
            ],
            [
                'key' => 'status',
                'value' => '状态'
            ]
        ];
    }

    public function export($query)
    {
        $list = $query->select(['sui.*', 'su.*'])
            ->orderBy(['su.status' => SORT_ASC, 'su.created_at' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($list as &$v) {
            $v['all_bonus'] = price_format($v['all_bonus']);
            $v['total_bonus'] = price_format($v['total_bonus']);
            $v['level_name'] = $v['level']['name'];
            $v['user_id'] = $v['user']['id'];
            $v['nickname'] = $v['user']['nickname'];
            $v['avatar'] = $v['user']['userInfo']['avatar'];
            $v['mobile'] = $v['user']['mobile'];
            unset($v['level']);
            unset($v['user']);
        }

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '股东管理列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }


    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['id'] = $item['id'];
            $arr['user_id'] = $item['user_id'];
            $arr['nickname'] = $item['nickname'];
            $arr['level_name'] = $item['level_name'];
            $arr['name'] = $item['name'];
            $arr['mobile'] = $item['mobile'];
            $arr['applyed_at'] = $this->getDateTime($item['applyed_at']);
            $arr['agreed_at'] = $this->getDateTime($item['agreed_at']);
            switch ($item['status']) {
                case 0:
                    $arr['status'] = '审核中';
                    break;
                case 1:
                    $arr['status'] = '审核通过';
                    break;
                case 2:
                    $arr['status'] = '审核拒绝';
                    break;
                default:
                    break;
            }
            $arr['all_bonus'] = price_format($item['all_bonus']);
            $arr['total_bonus'] = price_format($item['total_bonus']);

            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}