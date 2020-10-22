<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\models\OrderRefund;

class OrderRefundExport extends BaseExport
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
                'key' => 'name',
                'value' => '收件人',
            ],
            [
                'key' => 'mobile',
                'value' => '收件人电话',
            ],
            [
                'key' => 'address',
                'value' => '收件人地址',
            ],
            [
                'key' => 'merchant_remark',
                'value' => '商家备注',
            ],
            [
                'key' => 'refund_type',
                'value' => '售后类型',
            ],
            [
                'key' => 'refund_price',
                'value' => '退款金额',
            ],
            [
                'key' => 'apply_remark',
                'value' => '申请理由',
            ],
            [
                'key' => 'created_at',
                'value' => '申请售后时间',
            ],
            [
                'key' => 'refund_status',
                'value' => '售后状态',
            ],
            [
                'key' => 'user_express',
                'value' => '用户发货快递公司',
            ],
            [
                'key' => 'user_express_no',
                'value' => '用户发货快递单号',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->orderBy('created_at DESC')->with(['user.userInfo', 'order', 'detail'])
            ->asArray()
            ->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '售后订单' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];
        $number = 1;
        $orderRefund = new OrderRefund();
        foreach ($list as $key => $item) {
            $goodsInfo = \Yii::$app->serializer->decode($item['detail']['goods_info']);
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['user']['userInfo']['platform']);
            $arr['order_no'] = $item['order_no'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['goods_name'] = $goodsInfo['goods_attr']['name'];
            $arr['name'] = $item['order']['name'];
            $arr['mobile'] = $item['order']['mobile'];
            $arr['address'] = $item['order']['address'];
            $arr['merchant_remark'] = $item['merchant_remark'];
            $arr['refund_type'] = $item['type'] == 1 ? '退货退款' : '换货';
            $arr['refund_price'] = (float)$item['refund_price'];
            $arr['apply_remark'] = $item['remark'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['refund_status'] = $orderRefund->statusText($item);
            $arr['user_express'] = $item['express'];
            $arr['user_express_no'] = $item['express_no'];

            $attr = '';
            if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                foreach ($goodsInfo['attr_list'] as $attrItem) {
                    $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                }
            }
            $arr['attr'] = $attr;
            $arr['goods_num'] = (int)$item['detail']['num'];
            $arr['goods_no'] = $goodsInfo['goods_attr']['no'];
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
