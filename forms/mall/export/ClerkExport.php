<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\models\Order;
use app\models\UserCard;
use app\models\UserCoupon;

class ClerkExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'id',
                'value' => '核销员ID',
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'store_name',
                'value' => '所属门店',
            ],
            [
                'key' => 'clerk_order_number',
                'value' => '核销订单数',
            ],
            [
                'key' => 'clerk_order_monery',
                'value' => '核销总额',
            ],
            [
                'key' => 'clerk_card_number',
                'value' => '核销卡券次数',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->asArray()->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '核销员列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        $members = CommonMallMember::getAllMember();
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['user']['userInfo']['platform']);
            $arr['id'] = $item['user']['id'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['store_name'] = $item['store'][0]['name'];
            $arr['clerk_order_number'] = (int)$item['order_count'] ?: 0;
            $arr['clerk_card_number'] = (int)$item['card_count'] ?: 0;
            $arr['clerk_order_monery'] = floatval($item['order_sum']) ?: 0;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
