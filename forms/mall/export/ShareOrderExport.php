<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\ShareOrder;
use app\models\User;

class ShareOrderExport extends BaseExport
{
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
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'order_status',
                'value' => '订单状态',
            ],
            [
                'key' => 'pay_type',
                'value' => '支付方式',
            ],
            [
                'key' => 'is_pay',
                'value' => '支付状态',
            ],
            [
                'key' => 'pay_time',
                'value' => '支付时间',
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
                'key' => 'rebate',
                'value' => '自购返利',
            ],
            [
                'key' => 'first_user',
                'value' => '一级分销商',
            ],
            [
                'key' => 'second_user',
                'value' => '二级分销商',
            ],
            [
                'key' => 'third_user',
                'value' => '三级分销商',
            ],
            [
                'key' => 'first',
                'value' => '一级佣金',
            ],
            [
                'key' => 'second',
                'value' => '二级佣金',
            ],
            [
                'key' => 'third',
                'value' => '三级佣金',
            ],
        ];
    }

    public function export($query)
    {
        $query = $query->with(['user.userInfo', 'shareOrder', 'detail.share'])->orderBy(['created_at' => SORT_DESC]);

        $fileName = '分销订单列表' . \Yii::$app->mall->id;
        $filePath = \Yii::$app->basePath . '/web/temp/goods/' . $fileName . '.csv';

        if ($this->page == 1 && file_exists($filePath)) {
            unlink($filePath);
        }

        $list = $query->page($pagination, 50, $this->page)->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();
        (new CsvExport())->ajaxExport($dataList, $this->fieldsNameList, $fileName);

        if ($this->page > $pagination->page_count) {
            $download_url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/goods/' . $fileName . '.csv?time=' . time();
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
    }

    protected function transform($list)
    {
        $newList = [];
        // false 不拆分订单、true 根据商品数量拆分订单
        $sign = false;
        foreach ($this->fieldsKeyList as $item) {
            if (in_array($item, ['goods_name', 'attr', 'goods_num', 'goods_no'])) {
                $sign = true;
                break;
            }
        }

        $parentId = [];
        /** @var Order $item */
        foreach ($list as $item) {
            if (!in_array($item->shareOrder[0]->first_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->first_parent_id;
            }
            if (!in_array($item->shareOrder[0]->second_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->second_parent_id;
            }
            if (!in_array($item->shareOrder[0]->third_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->third_parent_id;
            }
        }
        /* @var User[] $parent */
        $parent = User::find()->where(['id' => $parentId])->with('share')->all();

        $order = new Order();
        // $number = 1;
        foreach ($list as $key => $item) {
            $arr = [];
            // $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = sprintf('%s(id:%s)', $item->user->nickname, $item->user->id);
            $arr['name'] = $item->name;
            $arr['mobile'] = $item->mobile;
            $arr['address'] = $item->address;
            $arr['created_at'] = $item->created_at;
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['pay_type'] = $order->getPayTypeText($item->pay_type);
            $arr['is_pay'] = $item->is_pay == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item->pay_time);
            $arr['words'] = $item->words;

            $orderForm = json_decode($item['order_form'], true);
            if ($orderForm) {
                $arr['remark'] = $orderForm;
            } else {
                $arr['remark'] = $item['remark'];
            }

            if ($sign) {
                foreach ($item->detail as $detailItem) {
                    // $arr['number'] = $number++;
                    $newArr = [];
                    $newArr['goods_name'] = isset($detailItem->goods->goodsWarehouse->name) ? $detailItem->goods->goodsWarehouse->name : '';
                    $newArr['goods_num'] = intval($detailItem->num);
                    $newArr['cost_price'] = isset($detailItem->goods->goodsWarehouse->cost_price) ? (float) $detailItem->goods->goodsWarehouse->cost_price : 0;
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
                    $newArr['total_price'] = (float) $detailItem->total_price;
                    $newArr['total_pay_price'] = (float) $detailItem->total_price;
                    $newArr = $this->getShareData($newArr, $detailItem->share, $parent);

                    $newList[] = array_merge($arr, $newArr);
                }
            } else {
                // $arr['number'] = $number++;
                $arr['total_price'] = (float) $item->total_price;
                $arr['total_pay_price'] = (float) $item->total_pay_price;

                $arr = $this->getShareData($arr, $item->shareOrder[0], $parent);
                $newList[] = $arr;
            }
        }
        $this->dataList = $newList;
    }

    private function getShareData($arr, $share, $parent)
    {
        if ($share['user_id'] == $share['first_parent_id']) {
            $arr['rebate'] = (float) $share['first_price'];

            $arr['first'] = (float) $share['second_price'];
            $arr['second'] = (float) $share['third_price'];
            $arr['third'] = 0;

            foreach ($parent as $user) {
                if ($user->id == $share['second_parent_id']) {
                    $arr['first_user'] = sprintf('id:%s  昵称:%s  姓名:%s  手机号:%s',$user->id, $user->nickname, $user->share->name, $user->share->mobile);
                }
                if ($user->id == $share['third_parent_id']) {
                    $arr['second_user'] = sprintf('id:%s  昵称:%s  姓名:%s  手机号:%s',$user->id, $user->nickname, $user->share->name, $user->share->mobile);
                }
            }

        } else {
            $arr['first'] = (float) $share['first_price'];
            $arr['second'] = (float) $share['second_price'];
            $arr['third'] = (float) $share['third_price'];
            foreach ($parent as $user) {
                if ($user->id == $share['first_parent_id']) {
                    $arr['first_user'] = sprintf('id:%s  昵称:%s  姓名:%s  手机号:%s',$user->id, $user->nickname, $user->share->name, $user->share->mobile);
                }
                if ($user->id == $share['second_parent_id']) {
                    $arr['second_user'] = sprintf('id:%s  昵称:%s  姓名:%s  手机号:%s',$user->id, $user->nickname, $user->share->name, $user->share->mobile);
                }
                if ($user->id == $share['third_parent_id']) {
                    $arr['third_user'] = sprintf('id:%s  昵称:%s  姓名:%s  手机号:%s',$user->id, $user->nickname, $user->share->name, $user->share->mobile);
                }
            }
        }

        return $arr;
    }
}
