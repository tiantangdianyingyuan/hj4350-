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

class MemberLogExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'pay_price',
                'value' => '支付金额',
            ],
            [
                'key' => 'pay_time',
                'value' => '支付日期',
            ],
            [
                'key' => 'detail',
                'value' => '购买情况',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy('created_at')->with('user.userInfo')->asArray()->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '会员购买记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['user']['userInfo']['platform']);
            $arr['order_no'] = $item['order_no'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['pay_price'] = (float)$item['pay_price'];
            $arr['pay_time'] = $this->getDateTime($item['pay_time']);

            $detail = \Yii::$app->serializer->decode($item['detail']);
            $arr['detail'] = $detail['before_update']['name'] . '->' . $detail['after_update'][count($detail['after_update']) - 1]['name'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
