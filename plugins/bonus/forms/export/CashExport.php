<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/15
 * Time: 14:48
 */

namespace app\plugins\bonus\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\User;
use app\plugins\bonus\models\BonusCaptain;
use app\plugins\bonus\models\BonusCash;
use app\plugins\bonus\models\BonusSetting;
use yii\helpers\ArrayHelper;

class CashExport extends BaseExport
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
                'value' => '昵称',
            ],
            [
                'key' => 'cash_price',
                'value' => '提现金额',
            ],
            [
                'key' => 'created_at',
                'value' => '申请日期',
            ],
            [
                'key' => 'bank_name',
                'value' => '银行名称',
            ],
            [
                'key' => 'account',
                'value' => '打款账号',
            ],
            [
                'key' => 'real_name',
                'value' => '真实姓名',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
            [
                'key' => 'pay_type',
                'value' => '打款方式',
            ],
            [
                'key' => 'pay_time',
                'value' => '打款时间',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy(['b.created_at' => SORT_DESC])->all();
        $newList = [];
        /* @var BonusCash[] $list */
        foreach ($list as $item) {
            $serviceCharge = round($item->price * $item->service_charge / 100, 2);
            $extra = \Yii::$app->serializer->decode($item->extra);
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'pay_type' => BonusSetting::PAY_TYPE_LIST[$item->type],
                'type' => $item->type,
                'status' => $item->status,
                'status_text' => $item->getStatusText($item->status),
                'user' => [
                    'avatar' => $item->user->userInfo->avatar,
                    'nickname' => $item->user->nickname,
                    'platform' => $item->user->userInfo->platform,
                ],
                'cash' => [
                    'price' => round($item->price, 2),
                    'service_charge' => $serviceCharge,
                    'actual_price' => round($item->price - $serviceCharge, 2)
                ],
                'extra' => [
                    'name' => $extra['name'] ? $extra['name'] : '',
                    'mobile' => $extra['mobile'] ? $extra['mobile'] : '',
                    'bank_name' => $extra['bank_name'] ? $extra['bank_name'] : ''
                ],
                'time' => [
                    'created_at' => $item->created_at,
                    'apply_at' => isset($extra['apply_at']) ? $extra['apply_at'] : '',
                    'remittance_at' => isset($extra['remittance_at']) ? $extra['remittance_at'] : '',
                    'reject_at' => isset($extra['reject_at']) ? $extra['reject_at'] : '',
                ],
                'content' => [
                    'apply_content' => isset($extra['apply_content']) ? $extra['apply_content'] : '',
                    'remittance_content' => isset($extra['remittance_content']) ? $extra['remittance_content'] : '',
                    'reject_content' => isset($extra['reject_content']) ? $extra['reject_content'] : '',
                ]
            ];
            $newList[] = $newItem;
        }

        $this->transform($newList);
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
        $name = '团队分红提现列表';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['user']['platform']);
            $arr['order_no'] = $item['order_no'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['cash_price'] = (float)$item['cash']['price'];
            $arr['created_at'] = $item['time']['created_at'];
            $arr['bank_name'] = $item['extra']['bank_name'];
            $arr['account'] = $item['extra']['mobile'];
            $arr['real_name'] = $item['extra']['name'];
            $arr['status'] = $item['status_text'];
            $arr['pay_type'] = $item['pay_type'];
            $arr['pay_time'] = $this->getDateTime($item['time']['remittance_at']);
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}