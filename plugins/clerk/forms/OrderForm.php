<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/8/21
 * Email: <657268722@qq.com>
 */

namespace app\plugins\clerk\forms;


use app\core\response\ApiCode;
use app\forms\common\CommonDistrict;
use app\events\OrderEvent;
use app\forms\common\order\CommonOrder;
use app\forms\mall\export\OrderExport;
use app\models\ClerkUser;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;

class OrderForm extends Model
{
    public $order_id;
    public $seller_remark;

    public $user_id;
    public $keyword;
    public $keyword_1;
    public $status;
    public $is_recycle;
    public $page;
    public $limit;
    public $is_offline;
    public $is_clerk;
    public $clerk_id;
    public $app_clerk;//手机端核销订单，取当前用户对应门店
    public $store_id;
    public $date_start;
    public $date_end;
    public $is_mch;
    public $mch_id;
    public $order_by;

    public $flag;
    public $fields;

    public $platform;//所属平台
    public $parent_id;

    // 前端操作 显示设置
    public $is_action_show;//所有更多操作
    public $is_send_show;
    public $is_cancel_show;
    public $is_clerk_show;

    public function rules()
    {
        return [
            [['order_id', 'is_mch', 'mch_id'], 'integer'],
            [['seller_remark', 'flag'], 'string'],
            [['keyword',], 'trim'],
            [['status', 'is_recycle', 'page', 'limit', 'user_id', 'is_offline', 'store_id',
                'keyword_1', 'platform', 'is_clerk'], 'integer'],
            [['status',], 'default', 'value' => -1],
            [['page',], 'default', 'value' => 1],
            [['date_start', 'date_end', 'fields', 'clerk_id'], 'trim'],
            [['is_action_show', 'is_send_show', 'is_cancel_show', 'is_clerk_show'], 'default', 'value' => 1],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        if (\Yii::$app->user->identity->mch_id) {
            $query->with('mch.store');
        }
        if (($this->app_clerk && $this->is_clerk == 1) || $this->clerk_id) {
            $this->order_by = 'confirm_time desc,';
        }
        // 自定义条件
        $query->andWhere($this->getExtraWhere());

        $list = $query->page($pagination)
            ->orderBy($this->order_by . 'o.created_at DESC')
            ->select(['o.*', 'u.nickname'])
            ->with(['detail.refund', 'detail.goods.goodsWarehouse'])
            ->with('clerk')
            ->with('user.userInfo')
            ->with('store')
            ->asArray()
            ->all();


        foreach ($list as &$item) {
            $item['platform'] = $item['user']['userInfo']['platform'];
            unset($item['user']);
            //插件名称
            if ($item['sign'] == '' && $item['mch_id'] == 0) {
                $item['plugin_name'] = '商城';
            } elseif ($item['mch_id'] > 0) {
                $item['plugin_name'] = '多商户';
            } else {
                $item['plugin_name'] = \Yii::$app->plugin->getPlugin($item['sign'])->getDisplayName();
            }

            $item['order_form'] = json_decode($item['order_form'], true);
            foreach ($item['detail'] as $key => &$detail) {
                $goods_info = \Yii::$app->serializer->decode($detail['goods_info']);
                $item['detail'][$key]['attr_list'] = $goods_info['attr_list'];

                $refund_status = 0;
                if ($detail['refund']) {
                    $refund_status = $detail['refund']['status'];
                }
                $item['detail'][$key]['refund_status'] = $refund_status;
                $detail['goods_info'] = \Yii::$app->serializer->decode($detail['goods_info']);

                //插件名称
                if ($detail['goods']['sign'] == '') {
                    $detail['plugin_name'] = '商城';
                } elseif ($detail['goods']['mch_id'] > 0) {
                    $detail['plugin_name'] = '多商户';
                } else {
                    $detail['plugin_name'] = \Yii::$app->plugin->getPlugin($detail['goods']['sign'])->getDisplayName();
                }
            }

            // 订单操作 是否显示
            $item['is_action_show'] = $this->is_action_show;
            $item['is_send_show'] = $this->is_send_show;
            $item['is_cancel_show'] = $this->is_cancel_show;
            $item['is_clerk_show'] = $this->is_clerk_show;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    protected function getExtraWhere()
    {
        return [
            'or',
            [
                'o.sign' => 'scan_code_pay',
                'o.is_pay' => 1,
                'o.is_sale' => 1,
                'o.is_confirm' => 1
            ],
            ['!=', 'o.sign', 'scan_code_pay']
        ];
    }


    protected function where()
    {
        $query = Order::find()->alias('o')->where([
            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0
        ])->leftJoin(['u' => User::tableName()], 'u.id = o.user_id');

        if (!$this->app_clerk && !$this->clerk_id) {
            if (\Yii::$app->user->identity->mch_id > 0) {
                $query->andWhere(['o.mch_id' => \Yii::$app->user->identity->mch_id]);
            } else {
                if ($this->is_mch) {
                    $query->andWhere(['>', 'o.mch_id', 0]);
                } else {
                    $query->andWhere(['o.mch_id' => 0]);
                }
            }
        }

        $query->keyword($this->status == -1, ['AND', ['o.is_recycle' => 0], ['not', ['o.cancel_status' => 1]]])
            ->keyword($this->status == 0, [
                'AND',
                ['o.is_pay' => 0, 'o.is_recycle' => 0],
                ['not', ['o.cancel_status' => 1]],
                ['o.is_send' => 0],
                ['o.sale_status' => 0],
            ])
            ->keyword($this->status == 1, [
                'AND',
                ['o.is_recycle' => 0, 'o.is_send' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['o.cancel_status' => 0],
                ['o.sale_status' => 0],
            ])
            ->keyword($this->status == 2, [
                'AND',
                ['o.is_send' => 1, 'o.is_confirm' => 0, 'o.is_recycle' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['not', ['o.cancel_status' => 1]],
                ['o.sale_status' => 0],
            ])
            ->keyword($this->status == 3, [
                'AND',
                ['o.is_send' => 1, 'o.is_confirm' => 1, 'o.is_recycle' => 0],
                ['or', ['o.is_pay' => 1], ['o.pay_type' => 2]],
                ['not', ['o.cancel_status' => 1]],
                ['o.sale_status' => 0],
                ['o.is_sale' => 1],
            ])
            ->keyword($this->status == 4, [
                'AND',
                ['o.cancel_status' => 2, 'o.is_recycle' => 0],
                ['o.sale_status' => 0],
            ])
            ->keyword($this->status == 5, ['o.is_recycle' => 0, 'o.cancel_status' => 1])
            ->keyword($this->status == 7, ['o.is_recycle' => 1])->keyword($this->sign, ['o.sign' => $this->sign])
            ->keyword($this->status == 8, [
                'AND',
                ['o.is_recycle' => 0, 'o.is_send' => 0],
                ['o.cancel_status' => 0]
            ]);


        ////////////////

        if ($this->user_id) {
            $query->andWhere(['o.user_id' => $this->user_id]);
        }
        if ($this->clerk_id) {
            $query->andWhere(['in', 'o.clerk_id', $this->clerk_id]);
        }
        //手机端取当前用户对应门店订单
        if ($this->app_clerk) {
            $clerk_info = ClerkUser::find()->andWhere(['user_id' => \Yii::$app->user->id, 'is_delete' => 0])->with('store')->asArray()->all();
            if (!empty($clerk_info)) {
                $arr = [];
                foreach ($clerk_info as $item) {
                    $arr[] = $item['store'][0]['id'];
                }
                if (!empty($arr)) {
                    $query->andWhere(['in', 'o.store_id', $arr]);
                }
            }
        }

        if ($this->store_id) {
            $query->andWhere(['o.store_id' => $this->store_id]);
        }

        if ($this->date_start) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end]);
        }

        if ($this->is_offline !== '' && $this->is_offline !== null) {
            $query->andWhere(['o.send_type' => $this->is_offline]);
        }

        if ($this->is_clerk == 1) {
            $query->andWhere(['>', 'o.clerk_id', 0]);
        }
        if ($this->is_clerk == 2) {
            $query->andWhere(['o.clerk_id' => 0])->andWhere([
                'or',
                ['o.pay_type' => 2],
                ['o.is_pay' => 1]
            ]);
        }
        // if ($this->parent_id) {
        //     $query->andWhere(['o.parent_id' => $page->parent_id]);
        // }

        // if (isset($page->platform) && ($page->platform == 1 || $page->platform == 0)) {
        //     $query->andWhere(['u.platform' => $page->platform]);
        // }

        if ($this->keyword) {
            switch ($this->keyword_1) {
                case 1:
                    $query->andWhere(['like', 'o.order_no', $this->keyword]);
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
                    // 门店搜索
                    $storeIds = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'name', $this->keyword])->select('id')->asArray()->all();
                    $arr = [];
                    foreach ($storeIds as $storeId) {
                        $arr[] = $storeId['id'];
                    }
                    $query->andWhere(['in', 'o.store_id', $arr]);
                    break;
                case 8:
                    // 门店搜索
                    $storeIds = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'name', $this->keyword])->select('id')->asArray()->all();
                    $arr = [];
                    foreach ($storeIds as $storeId) {
                        $arr[] = $storeId['id'];
                    }
                    $query->andWhere(['or', ['in', 'o.store_id', $arr], ['like', 'o.order_no', $this->keyword],
                        ['exists', (OrderDetail::find()->alias('od')
                            ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                                $query1->where(['like', 'gw.name', $this->keyword]);
                            }])->where("o.id = od.order_id"))]]);
                    break;
                default:
                    // 门店搜索
                    $storeIds = Store::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                        ->andWhere(['like', 'name', $this->keyword])->select('id')->asArray()->all();
                    $arr = [];
                    foreach ($storeIds as $storeId) {
                        $arr[] = $storeId['id'];
                    }
                    $query->andWhere(['or', ['like', 'o.order_no', $this->keyword], ['like', 'o.name', $this->keyword],
                        ['like', 'o.mobile', $this->keyword], ['like', 'u.nickname', $this->keyword], ['in', 'o.store_id', $arr],
                        ['exists', (OrderDetail::find()->alias('od')
                            ->innerJoinWith(['goodsWarehouse gw' => function ($query1) {
                                $query1->where(['like', 'gw.name', $this->keyword]);
                            }])->where("o.id = od.order_id"))]]);
            }

        }

        return $query;
    }

    public function sellerRemark()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
        ]);
        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }
        $order->seller_remark = $this->seller_remark;
        if ($order->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($order);
        }
    }

    public function confirm()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $order = Order::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->order_id,
            'is_delete' => 0
        ]);

        if (!$order) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }

        if ($order->status == 0) {
            throw new \Exception('订单进行中,不能进行操作');
        }

        try {
            CommonOrder::getCommonOrder($order->sign)->confirm($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function addressList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonDistrict();
        $list = $form->search();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function orderSales()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $order = Order::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->order_id,
                'is_delete' => 0
            ])->with('refund')->one();
            /* @var Order $order */
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->is_pay != 1) {
                throw new \Exception('订单未支付');
            }
            if ($order->is_confirm != 1) {
                throw new \Exception('订单未收货');
            }
            if ($order->is_sale == 1) {
                throw new \Exception('订单已过售后');
            }
            if ($order->refund) {
                foreach ($order->refund as $refund) {
                    if ($refund->status != 3 && $refund->is_confirm != 1) {
                        throw new \Exception('存在未完成的售后订单');
                    }
                }
            }
            $order->is_sale = 1;
            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }
            \Yii::$app->trigger(Order::EVENT_SALES, new OrderEvent([
                'order' => $order
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单结束'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
