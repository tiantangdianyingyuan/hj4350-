<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 16:49
 */

namespace app\plugins\advance\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\Order;
use app\models\User;
use yii\helpers\ArrayHelper;

class DepositOrderExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'advance_no',
                'value' => '定金订单号',
            ],
            [
                'key' => 'nickname',
                'value' => '下单用户',
            ],
            [
                'key' => 'goods_name',
                'value' => '商品名',
            ],
            [
                'key' => 'attr',
                'value' => '规格',
            ],
            [
                'key' => 'goods_num',
                'value' => '数量',
            ],
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'pay_type',
                'value' => '支付方式',
            ],
            [
                'key' => 'order_status',
                'value' => '订单状态',
            ],
            [
                'key' => 'is_pay',
                'value' => '付款状态',
            ],
            [
                'key' => 'pay_time',
                'value' => '付款时间',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query
            ->select(['o.*', 'u.nickname', 'ui.platform'])
            ->asArray()
            ->orderBy(['o.created_at' => SORT_DESC])->all();

        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newList[] = $newItem;
        }

        $this->transform($newList);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '预售定金订单列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $name = '预售定金订单列表';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        $order = new Order();
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['id'] = $item['id'];
            $arr['platform'] = $this->getPlatform($item['platform']);
            $arr['advance_no'] = $item['advance_no'];
            $arr['nickname'] = $item['nickname'];
            $arr['goods_name'] = $item['goods']['goodsWarehouse']['name'];
            $arr['attr'] = $item['goods']['attr_groups'];
            $arr['goods_num'] = $item['goods_num'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['pay_type'] = $order->getPayTypeText($item['pay_type']);
            $arr['order_status'] = $this->orderStatusText($item);
            $arr['is_pay'] = $item['is_pay'] == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item['pay_time']);
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    /**
     * @param null $order
     * @return string
     * @throws \Exception
     */
    public function orderStatusText($order = null)
    {
        if (!$order) {
            $order = $this;
        }
        if (!$order) {
            throw new \Exception('order不能为空');
        }
        if (is_array($order)) {
            $order = (object)$order;
        }

        try {
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                return '待付款';
            } elseif ($order->is_pay == 1 && $order->is_refund == 0 && $order->is_cancel == 0 && $order->is_delete == 0) {
                return '已完成';
            } elseif ($order->is_pay == 1 && $order->is_refund == 1) {
                return '已退款';
            } elseif ($order->is_cancel == 1) {
                return '已取消';
            } else {
                return '未知状态';
            }
        } catch (\Exception $exception) {
            return '未知状态';
        }
    }
}