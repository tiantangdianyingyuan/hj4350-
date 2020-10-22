<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\order;

use app\core\response\ApiCode;
use app\forms\common\CommonDelivery;
use app\forms\mall\export\OrderExport;
use app\forms\mall\export\OrderRefundExport;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\Store;
use app\models\User;
use app\models\OrderDetail;
use app\forms\common\prints\PrintOrder;
use app\models\RefundAddress;
use app\models\OrderRefund;
use app\models\Model;
use app\models\UserInfo;
use yii\helpers\ArrayHelper;

class OrderRefundListForm extends Model
{
    public $store_id;
    public $user_id;
    public $keyword;
    public $status;
    public $page;
    public $limit;
    public $date_start;
    public $date_end;
    public $keyword_1;

    public $platform;//所属平台
    public $fields;
    public $flag;
    public $refund_order_id;

    public function rules()
    {
        return [
            [['keyword', 'flag'], 'trim'],
            [['status', 'page', 'limit', 'user_id', 'keyword_1', 'refund_order_id'], 'integer'],
            [['status',], 'default', 'value' => -1],
            [['page',], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['fields'], 'safe'],
            [['date_start', 'date_end', 'fields', 'platform'], 'trim'],
        ];
    }

    public function search_num()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = $this->where();

        $count = $query->leftJoin(['od' => OrderDetail::tableName()], 'od.id = or.order_detail_id')
            ->andWhere(['od.refund_status' => 1])
            ->orderBy('created_at DESC')
            ->count();

        return $count;
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new OrderRefundExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination)->orderBy('created_at DESC')->all();
        $newList = [];
        /** @var OrderRefund $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['detail'] = ArrayHelper::toArray($item->detail);
            $newItem['order'] = ArrayHelper::toArray($item->order);
            $newItem['platform'] = $item->user->userInfo->platform;
            $newItem['nickname'] = $item->user->nickname;
            $newItem['status_text'] = $this->getOrderStatusText($item);
            $newItem['refund_type_text'] = $item->getRefundTypeText($item);
            $newItem['mobile'] = $item->mobile;
            $newItem['refund_data'] = $item->refund_data ? json_decode($item->refund_data, true) : [];

            //插件名称
            if ($item->order->sign == '' && $item->order->mch_id == 0) {
                $newItem['plugin_name'] = '商城';
            } elseif ($item->order->mch_id > 0) {
                $newItem['plugin_name'] = isset($item->order->mch->store->name) ? $item->order->mch->store->name : '多商户';
            } else {
                try {
                    $newItem['plugin_name'] = \Yii::$app->plugin->getPlugin($item->order->sign)->getDisplayName();
                } catch (\Exception $exception) {
                    $newItem['plugin_name'] = '未知插件';
                }
            }
            try {
                $goodsInfo = json_decode($item->detail->goods_info, true);
                $newItem['detail']['goods_info'] = $goodsInfo;
                $newItem['detail']['attr_list'] = $goodsInfo['attr_list'];
            } catch (\Exception $exception) {
                $newItem['detail']['goods_info'] = [];
                $newItem['detail']['attr_list'] = [];
            }

            try {
                $newItem['pic_list'] = json_decode($item->pic_list, true);
                $newItem['status_cn'] = (new OrderRefund())->statusText_business($item);
            } catch (\Exception $exception) {
                $newItem['pic_list'] = [];
                $newItem['status_cn'] = '';
            }
            $dataArr = $item->checkAfterRefund($item);
            $newItem = array_merge($newItem, $dataArr);
            $newItem['action_status'] = $item->getActionStatus($newItem);
            $newItem['send_type'] = $item->order->send_type;
            $newList[] = $newItem;
        };

        $address = RefundAddress::findAll([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0
        ]);
        foreach ($address as &$v) {
            if (mb_strlen($v->address) > 20) {
                $v->address = mb_substr($v->address, 0, 20) . '···';
            }
        }
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $newList,
                'address' => $address,
                'export_list' => (new OrderRefundExport())->fieldsList(),
            ]
        ];
    }

    protected function where()
    {
        $sign = \Yii::$app->request->get('sign');
        $query = OrderRefund::find()->alias('or')->where([
            'or.mall_id' => \Yii::$app->mall->id,
            'or.is_delete' => 0,
            'or.mch_id' => \Yii::$app->user->identity->mch_id,
        ])
            ->leftJoin(['u' => User::tableName()], 'u.id=or.user_id')
            ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id=or.user_id')
            ->leftJoin(['o' => Order::tableName()], 'o.id=or.order_id')
            ->with('order', 'user.userInfo');

        if ($sign != 'all') {
            $query->keyword($sign, ['o.sign' => $sign]);
        }

        $query->keyword($this->platform, ['ui.platform' => $this->platform]);

        if ($this->status == 0) {
            $query->andWhere(['or.status' => 1]);
        }
        if ($this->status == 1) {
            $query->andWhere(['or.status' => 2, 'or.is_send' => 0]);
        }
        if ($this->status == 2) {
            $query->andWhere(['or.status' => 2, 'or.is_send' => 1])->andWhere([
                'OR',
                ['or.is_confirm' => 0, 'or.type' => 2],
                ['or.is_refund' => 0, 'or.type' => [1, 3]],
            ]);
        }
        if ($this->status == 3) {
            $query->andWhere([
                'OR',
                ['or.type' => [1, 3], 'or.is_confirm' => 1, 'or.is_refund' => 1],
                ['or.status' => 3],
                ['or.type' => 2, 'or.is_confirm' => 1],
            ]);
        }

        if ($this->user_id) {
            $query->andWhere(['o.user_id' => $this->user_id]);
        }

        if ($this->store_id) {
            $query->andWhere(['o.store_id' => $this->store_id]);
        }

        if ($this->date_start) {
            $query->andWhere(['>=', 'or.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'or.created_at', $this->date_end]);
        }

        if ($this->keyword) {
            switch ($this->keyword_1) {
                case 1:
                    $query->andWhere(['like', 'or.order_no', $this->keyword]);
                    break;
                case 2:
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                    break;
                case 3:
                    $query->andWhere(['like', 'o.name', $this->keyword]);
                    break;
                case 4:
                    $query->andWhere(['u.id' => $this->keyword]);
                    break;
                case 5:
                    $query->andWhere(['exists', (OrderDetail::find()->alias('od')
                        ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                            $query1->where(['like', 'gw.name', $this->keyword]);
                        }])->where("o.id = od.order_id"))]);
                    break;
                case 6:
                    $query->andWhere(['like', 'o.mobile', $this->keyword]);
                    break;
                case 7:
                    // 门店名称搜索
                    $storeIds = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'name', $this->keyword])->select('id');
                    $query->andWhere(['o.store_id' => $storeIds]);
                    break;
                case 8:
                    /** @var Order $order */
                    $order = Order::find()->where(['order_no' => $this->keyword])->one();
                    if ($order) {
                        $query->andWhere(['or.order_id' => $order->id]);
                    }
                    break;
                default:
                    $query->andWhere(['or', ['like', 'or.order_no', $this->keyword], ['like', 'o.name', $this->keyword],
                        ['like', 'o.mobile', $this->keyword], ['like', 'u.nickname', $this->keyword], ['like', 'o.order_no', $this->keyword],
                        ['exists', (OrderDetail::find()->alias('od')
                            ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                                $query1->where(['like', 'gw.name', $this->keyword]);
                            }])->where("o.id = od.order_id"))]]);
            }
        }

        return $query;
    }


    public function refundDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'id' => $this->refund_order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id
            ])->with('detail', 'user', 'order', 'refundAddress')->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }
            $newOrderRefund = ArrayHelper::toArray($orderRefund);
            $newOrderRefund['pic_list'] = \Yii::$app->serializer->decode($orderRefund->pic_list);
            $newOrderRefund['detail'] = $orderRefund->detail ? ArrayHelper::toArray($orderRefund->detail) : [];
            $newOrderRefund['user'] = $orderRefund->user ? ArrayHelper::toArray($orderRefund->user) : [];
            $newOrderRefund['order'] = $orderRefund->order ? ArrayHelper::toArray($orderRefund->order) : [];
            $newOrderRefund['refundAddress'] = $orderRefund->refundAddress ? ArrayHelper::toArray($orderRefund->refundAddress) : [];
            $newOrderRefund['refund_type_text'] = $orderRefund->getRefundTypeText($orderRefund);
            $newOrderRefund['refund_process'] = $this->getRefundProcess($orderRefund);
            $newOrderRefund['refund_data'] = $orderRefund->refund_data ? json_decode($orderRefund->refund_data, true) : [];
            if ($newOrderRefund['order']['send_type'] == 2) {
                $commonDelivery = CommonDelivery::getInstance();
                $cityConfig = $commonDelivery->getConfig();
                $newOrderRefund['order']['city']['address'] = $cityConfig['address']['address'] ?? '';
                $newOrderRefund['order']['city']['explain'] = $cityConfig['explain'] ?? '';
                $newOrderRefund['order']['city']['contact_way'] = $cityConfig['contact_way'] ?? '';
            }
            if (!empty($newOrderRefund['detail'])) {
                $newOrderRefund['detail']['form_data'] = json_decode($newOrderRefund['detail']['form_data'], true);
                $newOrderRefund['order']['goods_type'] = $newOrderRefund['detail']['goods_type'];
            } else {
                $newOrderRefund['order']['goods_type'] = '';
            }
            $newOrderRefund['status_cn'] = (new OrderRefund())->statusText_business($newOrderRefund);

            if (isset($newOrderRefund['detail']['goods_info'])) {
                $newOrderRefund['detail']['goods_info'] = \Yii::$app->serializer->decode($newOrderRefund['detail']['goods_info']);
            }
            $newOrderRefund = array_merge($newOrderRefund, $orderRefund->checkAfterRefund($orderRefund));

            $address = RefundAddress::findAll([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0
            ]);
            $newOrderRefund['action_status'] = $orderRefund->getActionStatus($newOrderRefund);
            foreach ($address as &$v) {
                if (mb_strlen($v->address) > 20) {
                    $v->address = mb_substr($v->address, 0, 20) . '···';
                }
            }
            unset($v);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newOrderRefund,
                    'address' => $address
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 后台订单状态
     * @param OrderRefund $orderRefund
     * @return string
     */
    private function getOrderStatusText($orderRefund)
    {
        if ($orderRefund->status === 1) {
            $status = '待审核';
        } else if ($orderRefund->status === 3) {
            $status = '已完成';
        } else if ($orderRefund->status === 2 && $orderRefund->is_send === 0 && $orderRefund->type !== 3) {
            $status = '待买家发货';
        } else if ($orderRefund->status === 2 && $orderRefund->is_send === 1 && $orderRefund->is_confirm === 0 && $orderRefund->type !== 3) {
            $status = '待卖家收货';
        } else if ($orderRefund->status === 2 && ($orderRefund->type === 1 || $orderRefund->type === 3) && $orderRefund->is_confirm === 1 && $orderRefund->is_refund === 0) {
            $status = '待卖家退款';
        } else if ($orderRefund->status === 2 && ($orderRefund->type === 2 && $orderRefund->is_confirm === 1) || (($orderRefund->type === 1 || $orderRefund->type === 3) && $orderRefund->is_confirm === 1 && ($orderRefund->is_refund === 1 || $orderRefund->is_refund === 2))) {
            $status = '已完成';
        } else {
            $status = '状态未知';
        }

        return $status;
    }

    /**
     * 后台售后订单 售后流程
     */
    private function getRefundProcess($orderRefund)
    {
        $array[] = [
            'name' => '买家',
            'time' => $orderRefund->created_at,
            'text' => '买家申请售后'
        ];

        if ($orderRefund->status != 1) {
            array_unshift($array, [
                'name' => '卖家',
                'time' => $orderRefund->status_time,
                'text' => $orderRefund->status == 2 ? '卖家同意售后' : '卖家拒绝售后',
            ]);
        }

        if ($orderRefund->status == 2 && $orderRefund->is_send == 1 && $orderRefund->type != 3 && $orderRefund->order->send_type != 1 && $orderRefund->order->send_type != 2) {
            array_unshift($array, [
                'name' => '买家',
                'time' => $orderRefund->send_time,
                'text' => '买家已发货',
            ]);
        }

        if ($orderRefund->status == 2) {
            if ($orderRefund->is_confirm == 1 && $orderRefund->type == 1 && $orderRefund->type != 3) {
                array_unshift($array, [
                    'name' => '卖家',
                    'time' => $orderRefund->confirm_time,
                    'text' => '确认收货',
                ]);
            }

            if ($orderRefund->is_confirm == 1 && ($orderRefund->type == 1 || $orderRefund->type == 3) && ($orderRefund->is_refund == 1 || $orderRefund->is_refund == 2)) {
                array_unshift($array, [
                    'name' => '卖家',
                    'time' => $orderRefund->is_refund == 1 ? $orderRefund->refund_time : $orderRefund->confirm_time,
                    'text' => '卖家退款给买家',
                ]);
            }
        }


        if ($orderRefund->status == 2 && $orderRefund->is_confirm == 1 && $orderRefund->type == 2) {
            array_unshift($array, [
                'name' => '卖家',
                'time' => $orderRefund->confirm_time,
                'text' => '卖家发货',
            ]);
        }

        return $array;
    }
}
