<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;

class OrderExport extends BaseExport
{
    public $send_type;

    public $page;

    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'pay_order_no',
                'value' => '商户单号',
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
                'key' => 'send_type',
                'value' => '发货方式',
            ],
            [
                'key' => 'express',
                'value' => '快递公司',
            ],
            [
                'key' => 'express_no',
                'value' => '快递单号',
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
                'key' => 'city_name',
                'value' => '配送员姓名',
            ],
            [
                'key' => 'city_mobile',
                'value' => '配送员电话',
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
                'key' => 'words',
                'value' => '卖家留言',
            ],
            [
                'key' => 'seller_remark',
                'value' => '商家备注',
            ],
            [
                'key' => 'remark',
                'value' => '备注/表单',
            ],
            [
                'key' => 'clerk_remark',
                'value' => '核销备注',
            ],
            [
                'key' => 'price',
                'value' => '售价',
            ],
            [
                'key' => 'original_price',
                'value' => '原价',
            ],
        ];

        if (\Yii::$app->user->identity->mch_id > 0) {
            foreach ($fieldsList as $key => $item) {
                if ($item['key'] == 'city_name' || $item['key'] == 'city_mobile') {
                    unset($fieldsList[$key]);
                }
            }
            $fieldsList = array_values($fieldsList);
        }

        try {
            $plugins = \Yii::$app->role->getPluginList();
            foreach ($plugins as $plugin) {
                if (method_exists($plugin, 'getOrderExportFields')) {
                    $list = $plugin->getOrderExportFields();
                    $pluginName = $plugin->getName();
                    foreach ($list as $key => $value) {
                        $fieldsList[] = $value;
                    }
                }
            }
        } catch (\Exception $exception) {
            \Yii::warning('订单导出异常' . $exception->getMessage());
        }

        return $fieldsList;
    }

    public function export($query)
    {
        try {
            \Yii::$app->plugin->getPlugin('mch');
            $query->with('mch.store', 'detail.goods.mch.store');
        } catch (\Exception $exception) {

        }
        $query->with(['user.userInfo', 'clerk', 'store', 'detail.goods.goodsWarehouse', 'refund', 'paymentOrder.paymentOrderUnion', 'detail.expressRelation.orderExpress', 'detailExpress'])
            ->orderBy('o.created_at DESC');

        try {

            $filePath = \Yii::$app->basePath . '/web/temp/goods/' . $this->getFileName() . '.csv';

            if ($this->page == 1 && file_exists($filePath)) {
                unlink($filePath);
            }

            $list = $query->page($pagination, 50, $this->page)->all();

            $this->transform($list);
            $this->getFields();
            $dataList = $this->getDataList();
            (new CsvExport())->ajaxExport($dataList, $this->fieldsNameList, $this->getFileName());

            if ($this->page > $pagination->page_count) {
                $download_url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/goods/' .$this->getFileName() . '.csv?time=' . time();
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
            $name = '订单列表-快递配送';
        } elseif ($this->send_type == 1) {
            $name = '订单列表-自提';
        } elseif ($this->send_type == 2) {
            $name = '订单列表-同城配送';
        } else {
            $name = '订单列表';
        }
        $fileName = $name . \Yii::$app->mall->id;

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        // false 不拆分订单、true 根据商品数量拆分订单
        $sign = false;
        $this->fieldsKeyList = $this->fieldsKeyList ?: [];
        foreach ($this->fieldsKeyList as $item) {
            if (in_array($item, ['goods_name', 'attr', 'goods_num', 'goods_no', 'cost_price', 'send_type', 'express', 'express_no', 'city_name', 'city_mobile'])) {
                $sign = true;
                break;
            }
        }
        $order = new Order();
        /** @var Order $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['pay_order_no'] = isset($item->paymentOrder->paymentOrderUnion->order_no) ? $item->paymentOrder->paymentOrderUnion->order_no : '';
            $arr['nickname'] = sprintf('%s(id:%s)', $item->user->nickname, $item->user->id);
            $arr['name'] = $item->name;
            $arr['mobile'] = $item->mobile;
            $arr['address'] = $item->address;
            $arr['created_at'] = $item->created_at;
            $arr['pay_type'] = $order->getPayTypeText($item->pay_type);
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['is_pay'] = $item->is_pay == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item->pay_time);
            $arr['is_send'] = $item->is_send == 1 ? "已发货" : "未发货";
            $arr['send_time'] = $this->getDateTime($item->send_time);
            $arr['is_confirm'] = $item->is_confirm == 1 ? "已收货" : "未收货";
            $arr['confirm_time'] = $this->getDateTime($item->confirm_time);
            $arr['words'] = $item->words;
            $arr['seller_remark'] = $item->seller_remark;

            $arr['express_price'] = $item->express_price > 0 ? floatval($item->express_price) : 0;
            if ($item->send_type == 1) {
                $arr['clerk_name'] = $item->clerk ? $item->clerk->nickname : '';
                $arr['store_name'] = $item->store ? $item->store->name : '';
            }
            $arr['clerk_remark'] = empty($item->orderClerk) ? '' : $item->orderClerk->clerk_remark;
            if ($item->mch_id > 0) {
                $arr['store_name'] = $item->mch->store->name;
            }

            $orderForm = json_decode($item->order_form, true);
            $orderFormList = [];
            if ($orderForm) {
                $orderFormList[] = [
                    'id' => -1,
                    'form_data' => $orderForm,
                ];
            } else {
                /** @var OrderDetail $dItem */
                foreach ($item->detail as $dItem) {
                    $orderFormList[] = [
                        'id' => $dItem['id'],
                        'form_data' => $dItem->form_data ? json_decode($dItem->form_data, true) : [],
                    ];
                }
            }
            $arr = array_merge($arr, $this->setPluginData($item));
            if ($sign) {
                /** @var OrderDetail $detailItem */
                foreach ($item->detail as $index => $detailItem) {
                    $newArr = [];
                    // 拆分展示的订单 运费只在第一条订单展示
                    if ($index > 0) {
                        $arr['express_price'] = 0;
                    }
                    // 规格详情
                    $goodsInfo = \Yii::$app->serializer->decode($detailItem->goods_info);
                    $attr = '';
                    if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                        foreach ($goodsInfo['attr_list'] as $attrItem) {
                            $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                        }
                    }
                    $newArr['goods_name'] = isset($goodsInfo['goods_attr']['name']) ? $goodsInfo['goods_attr']['name'] : $detailItem->goods->name;
                    $newArr['goods_num'] = intval($detailItem->num);
                    $newArr['cost_price'] = isset($detailItem->goods->goodsWarehouse->cost_price) ? (float)$detailItem->goods->goodsWarehouse->cost_price : 0;
                    $newArr['original_price'] = price_format(isset($goodsInfo['goods_attr']['original_price']) ? $goodsInfo['goods_attr']['original_price'] : $detailItem->goods->originalPrice);
                    $newArr['price'] = price_format(isset($goodsInfo['goods_attr']['price']) ? $goodsInfo['goods_attr']['price'] : $detailItem->goods->price);

                    $newArr['attr'] = $attr;
                    $newArr['goods_no'] = isset($goodsInfo['goods_attr']['no']) ? $goodsInfo['goods_attr']['no'] : '';
                    $newArr['total_price'] = (float) $detailItem->total_original_price;
                    $newArr['total_pay_price'] = (float) $detailItem->total_price;
                    foreach ($orderFormList as $ofItem) {
                        if ($ofItem['id'] == $detailItem->id) {
                            $newArr['remark'] = $ofItem['form_data'] ?: $item->remark;
                        } elseif ($ofItem['id'] == -1) {
                            $newArr['remark'] = $ofItem['form_data'];
                        }
                    }
                    // 物流信息
                    $newArr['send_type'] = $item->getSendType($item);
                    $newArr['express'] = $item->express;
                    $newArr['express_no'] = $item->express_no;
                    $newArr['city_name'] = $item->city_name;
                    $newArr['city_mobile'] = $item->city_mobile;
                    if ($detailItem->expressRelation) {
                        $orderExpress = $detailItem->expressRelation->orderExpress;
                        $newArr['express'] = $orderExpress->send_type == 1 ? $orderExpress->express : '其它方式';
                        $newArr['express_no'] = $orderExpress->send_type == 1 ? $orderExpress->express_no : $orderExpress->express_content;

                        $newArr['city_name'] = $detailItem->expressRelation->orderExpress->city_name;
                        $newArr['city_mobile'] = $detailItem->expressRelation->orderExpress->city_mobile;
                    }

                    $newItem = array_merge($newArr, $arr);
                    // 分批发货时 特殊处理
                    if ($newItem['order_status'] == '待发货' && $detailItem->expressRelation) {
                        $newItem['order_status'] = '已发货';
                    }
                    $newList[] = $newItem;
                }
            } else {
                $arr['total_price'] = (float) $item->total_price;
                $arr['total_pay_price'] = (float) $item->total_pay_price;
                $newFormData = [];
                foreach ($orderFormList as $ofItem2) {
                    if (!is_array($ofItem2['form_data'])) {
                        $ofItem2['form_data'] = [];
                    }
                    $newFormData = array_merge($ofItem2['form_data'], $newFormData);
                }
                $arr['remark'] = $newFormData ?: $item->remark;
                // 分批发货时 特殊处理
                if ($arr['order_status'] == '待发货' && count($item->detailExpress)) {
                    $arr['order_status'] = '部分发货';
                }
                $newList[] = $arr;
            }
        }

        $this->dataList = $newList;
    }

    private function setPluginData($item)
    {
        try {
            $list = [];
            if ($item->sign) {
                $plugin = \Yii::$app->plugin->getPlugin($item->sign);
                if ($plugin && method_exists($plugin, 'getOrderExportData')) {
                    $params = [
                        'order_id' => $item->id,
                    ];
                    $list = $plugin->getOrderExportData($params);
                }
            }
        } catch (\Exception $exception) {

        }

        return $list;
    }

    protected function getIsAddNumber()
    {
        return false;
    }
}
