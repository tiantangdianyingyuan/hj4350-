<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\bonus\forms\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\forms\mall\export\BaseExport;
use app\models\Order;

class OrderExport extends BaseExport
{
    public $send_type;

    public $page;

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
                'key' => 'cost_price',
                'value' => '成本价',
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
                'key' => 'total_price',
                'value' => '总金额',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '实际付款',
            ],
            [
                'key' => 'express_price',
                'value' => '运费',
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
                'key' => 'store_name',
                'value' => '门店',
            ],
            [
                'key' => 'clerk_name',
                'value' => '核销人',
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
                'key' => 'is_send',
                'value' => '发货状态',
            ],
            [
                'key' => 'send_time',
                'value' => '发货时间',
            ],
            [
                'key' => 'is_confirm',
                'value' => '收货状态',
            ],
            [
                'key' => 'confirm_time',
                'value' => '收货时间',
            ],
            [
                'key' => 'bonus_remark',
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
            [
                'key' => 'captain_name',
                'value' => '队长',
            ],
            [
                'key' => 'captain_mobile',
                'value' => '队长电话',
            ],
            [
                'key' => 'bonus_price',
                'value' => '分红金额',
            ],
            [
                'key' => 'bonus_status',
                'value' => '分红状态',
            ],
        ];
    }

    public function export($query)
    {
        $query = $query->with(['user.userInfo', 'clerk', 'store', 'detail.goods.goodsWarehouse', 'refund'])
            ->select(['o.*', 'u.nickname', 'bo.remark as bonus_remark', 'bc.name as captain_name', 'bc.mobile as captain_mobile', 'bo.bonus_price', 'bo.status as bonus_status'])
            ->orderBy('o.created_at DESC');

        try {

            $filePath = \Yii::$app->basePath . '/web/temp/goods/' . $this->getFileName() . '.csv';

            if ($this->page == 1 && file_exists($filePath)) {
                unlink($filePath);
            }

            $list = $query->page($pagination, 50, $this->page)->asArray()->all();

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
        if ($this->send_type == 0) {
            $name = '分红订单-快递配送';
        } elseif ($this->send_type == 1) {
            $name = '分红订单-自提';
        } elseif ($this->send_type == 2) {
            $name = '分红订单-同城配送';
        } else {
            $name = '分红订单';
        }

        $fileName = $name;

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
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item['user']['userInfo']['platform']);
            $arr['order_no'] = $item['order_no'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['name'] = $item['name'];
            $arr['mobile'] = $item['mobile'];
            $arr['address'] = $item['address'];
            $arr['created_at'] = $item['created_at'];
            $arr['pay_type'] = $order->getPayTypeText($item['pay_type']);
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['is_pay'] = $item['is_pay'] == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item['pay_time']);
            $arr['is_send'] = $item['is_send'] == 1 ? "已发货" : "未发货";
            $arr['send_time'] = $this->getDateTime($item['send_time']);
            $arr['is_confirm'] = $item['is_confirm'] == 1 ? "已收货" : "未收货";
            $arr['confirm_time'] = $this->getDateTime($item['confirm_time']);
            $arr['words'] = $item['words'];
            $arr['seller_remark'] = $item['seller_remark'];

            if ($item['send_type'] == 1) {
                $arr['clerk_name'] = $item['clerk'] ? $item['clerk']['nickname'] : '';
                $arr['store_name'] = $item['store'] ? $item['store']['name'] : '';
            } else {
                $arr['express_price'] = $item['express_price'];
                $arr['express_no'] = $item['express_no'];
                $arr['express'] = $item['express'];
            }
            $arr['bonus_remark'] = $item['bonus_remark'];
            $arr['captain_name'] = $item['captain_name'];
            $arr['captain_mobile'] = $item['captain_mobile'];
            $arr['bonus_price'] = $item['bonus_price'];
            $arr['bonus_status'] = $item['bonus_status'] != 0 ? "完成" : "进行中";

            if ($sign) {
                foreach ($item['detail'] as $detailItem) {
                    $arr['number'] = $number++;
                    $newArr['goods_name'] = $detailItem['goods']['goodsWarehouse']['name'];
                    $newArr['goods_num'] = (int) $detailItem['num'];
                    $newArr['cost_price'] = (float) $detailItem['goods']['goodsWarehouse']['cost_price'];
                    // 规格详情
                    $goodsInfo = \Yii::$app->serializer->decode($detailItem['goods_info']);
                    $attr = '';
                    if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                        foreach ($goodsInfo['attr_list'] as $attrItem) {
                            $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                        }
                    }
                    $newArr['attr'] = $attr;
                    $newArr['goods_no'] = isset($goodsInfo['goods_attr']['no']) ? $goodsInfo['goods_attr']['no'] : '';
                    $newArr['total_price'] = (float) $detailItem['total_original_price'];
                    $newArr['total_pay_price'] = (float) $detailItem['total_price'];

                    $newList[] = array_merge($newArr, $arr);
                }
            } else {
                $arr['number'] = $number++;
                $arr['total_price'] = (float) $item['total_price'];
                $arr['total_pay_price'] = (float) $item['total_pay_price'];
                $newList[] = $arr;
            }
        }
        $this->dataList = $newList;
    }
}
