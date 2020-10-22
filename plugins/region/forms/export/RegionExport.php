<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/9
 * Time: 15:00
 */

namespace app\plugins\region\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\plugins\region\forms\common\CommonRegion;
use app\plugins\region\models\RegionUser;
use yii\helpers\ArrayHelper;

class RegionExport extends BaseExport
{
    public function fieldsList()
    {
        return [
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
                'value' => '代理级别',
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
        $list = $query
            ->orderBy(['ru.created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /**@var RegionUser $item * */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['all_bonus'] = $item->regionInfo->all_bonus;
            $newItem['total_bonus'] = $item->regionInfo->total_bonus;
            $newItem['level_name'] = CommonRegion::getInstance()->parseLevel($item->level);
            $newItem['user_id'] = $item->user_id;
            $newItem['nickname'] = $item->user->nickname;
            $newItem['avatar'] = $item->user->userInfo->avatar;
            $newItem['mobile'] = $item->regionInfo->phone ?? $item->user->mobile;
            $newItem['name'] = $item->regionInfo->name;
            $newList[] = $newItem;
        }

        $this->transform($newList);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '区域代理管理列表' . date('YmdHis');
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
