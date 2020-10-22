<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\Order;
use app\models\OrderRefund;

class OrderExport extends BaseExport
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
                'key' => 'goods_no',
                'value' => '货号',
            ],
            [
                'key' => 'total_price',
                'value' => '总金额',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '实际付款',
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
            [
                'key' => 'remark',
                'value' => '备注/表单',
            ],
            [
                'key' => 'words',
                'value' => '买家留言',
            ],
            [
                'key' => 'seller_remark',
                'value' => '商家备注',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->with(['user.userInfo', 'detail.goods.goodsWarehouse'])
            ->orderBy('o.created_at DESC')
            ->all();

        $this->transform($list);
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
        $name = '当面付';
        $fileName = $name . date('YmdHis');

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        // false 不拆分订单、true 根据商品数量拆分订单
        $sign = false;
        foreach ($this->fieldsKeyList as $item) {
            if (in_array($item, ['goods_name', 'attr', 'goods_num', 'goods_no', 'cost_price'])) {
                $sign = true;
                break;
            }
        }
        $order = new Order();
        $number = 1;
        /** @var Order $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = $item->user->nickname;
            $arr['name'] = $item->name;
            $arr['created_at'] = $item->created_at;
            $arr['pay_type'] = $order->getPayTypeText($item->pay_type);
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['is_pay'] = $item->is_pay == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item->pay_time);
            $arr['words'] = $item->words;
            $arr['seller_remark'] = $item->seller_remark;
            // TODO 下单表单待完善
            $arr['remark'] = $item->remark;

            if ($sign) {
                foreach ($item->detail as $detailItem) {
                    $arr['number'] = $number++;
                    $newArr['goods_name'] = $detailItem->goods->goodsWarehouse->name;
                    $newArr['goods_num'] = (int)$detailItem->num;
                    // 规格详情
                    $goodsInfo = \Yii::$app->serializer->decode($detailItem->goods_info);
                    $attr = '';
                    if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                        foreach ($goodsInfo['attr_list'] as $attrItem) {
                            $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                        }
                    }
                    $newArr['attr'] = $attr;
                    $newArr['goods_no'] = isset($goodsInfo['goods_attr']['no']) ? $goodsInfo['goods_attr']['no'] : '';
                    $newArr['total_price'] = (float)$detailItem->total_original_price;
                    $newArr['total_pay_price'] = (float)$detailItem->total_price;

                    $newList[] = array_merge($newArr, $arr);
                }
            } else {
                $arr['number'] = $number++;
                $arr['total_price'] = (float)$item->total_price;
                $arr['total_pay_price'] = (float)$item->total_pay_price;
                $newList[] = $arr;
            }
        }
        $this->dataList = $newList;
    }
}
