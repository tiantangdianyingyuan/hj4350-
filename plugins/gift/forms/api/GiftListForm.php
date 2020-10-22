<?php
/**
 * Created by zjhj_mall_v4_gift
 * User: jack_guo
 * Date: 2019/10/17
 * Email: <657268722@qq.com>
 */

namespace app\plugins\gift\forms\api;


use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftUserOrder;

class GiftListForm extends Model
{
    public $gift_id;
    public $user_order_id;
    public $page;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['gift_id', 'user_order_id', 'page'], 'integer'],
        ];
    }

    //我送出的
    public function getSendList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $list = GiftLog::find()->andWhere([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_pay' => 1
            ])->with('winUser.user.userInfo')
                ->with('user.userInfo')
                ->with('sendOrder.detail.goods.goodsWarehouse')
                ->orderBy('created_at desc')
                ->page($pagination)
                ->asArray()->all();

            //状态
            foreach ($list as &$item) {
                $item['status'] = '';
                switch ($item['type']) {
                    case 'direct_open':
                        //一人拿所有奖品
                        if ($item['open_type'] == 0) {
                            if (count($item['winUser']) > 0) {
                                $item['status'] = '已完成';
                            } elseif (count($item['winUser']) <= 0 && strtotime($item['auto_refund_time']) > time()) {
                                $item['status'] = '等待领取';
                            } elseif (count($item['winUser']) == 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '领取失败';
                            }
                        } else {
                            if ((count($item['winUser']) > 0 && strtotime($item['auto_refund_time']) < time())
                                || (count($item['winUser']) == $item['num'])) {
                                $item['status'] = '已完成';
                            } elseif (count($item['winUser']) <= $item['num'] && strtotime($item['auto_refund_time']) > time()) {
                                $item['status'] = '等待领取';
                            } elseif (count($item['winUser']) == 0 && strtotime($item['auto_refund_time']) < time()) {
                                $item['status'] = '领取失败';
                            }
                        }
                        break;
                    case 'time_open':
                        if (count($item['winUser']) > 0 && strtotime($item['open_time']) < time()) {
                            $item['status'] = '已完成';
                        } elseif (strtotime($item['open_time']) > time()) {
                            $item['status'] = '等待开奖';
                        } elseif (count($item['winUser']) == 0 && strtotime($item['open_time']) < time()) {
                            $item['status'] = '开奖失败';
                        }
                        break;
                    case 'num_open':
//                        if (count($item['winUser']) >= $item['open_num'] && strtotime($item['auto_refund_time']) < time()) {
                        if (count($item['winUser']) >= $item['open_num']) {
                            $item['status'] = '已完成';
                        } elseif (count($item['winUser']) < $item['open_num'] && strtotime($item['auto_refund_time']) > time()) {
                            $item['status'] = '等待开奖';
                        } elseif (count($item['winUser']) < $item['open_num'] && strtotime($item['auto_refund_time']) < time()) {
                            $item['status'] = '开奖失败';
                        }
                        break;
                }

                //判断礼包商品数量
                $item['is_big_gift'] = 0;
                if (@count($item['sendOrder']) > 1 || @count($item['sendOrder'][0]['detail']) > 1) {
                    $item['is_big_gift'] = 1;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function getSendDetail()
    {
        bcscale(2);
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $detail = GiftLog::find()->andWhere([
                'is_delete' => 0,
                'id' => $this->gift_id,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id
            ])->with('winUser.giftOrder')
                ->with('sendOrder.detail.goods.goodsWarehouse')
                ->asArray()
                ->one();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $this->dataReturn($detail),
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }

    }

    //我参与的
    public function getJoinList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftUserOrder::find()->alias('guo')->select(['guo.*'])
                ->andWhere([
                    'guo.mall_id' => \Yii::$app->mall->id,
                    'guo.is_delete' => 0,
                    'guo.user_id' => \Yii::$app->user->id
                ])->rightJoin(['gl' => GiftLog::tableName()], "gl.id = guo.gift_id and gl.type <> 'direct_open'")
                ->with('giftLog.sendOrder.detail.goods.goodsWarehouse')
                ->with('giftOrder.order.store')
                ->with('giftOrder.order.detail.goods.goodsWarehouse')
                ->with('giftOrder.order.detail.orderRefund')
                ->with('giftOrder.order.detailExpress.expressRelation.orderDetail')
                ->with('giftOrder.goods.goodsWarehouse')
                ->with('giftOrder.goodsAttr')
                ->with('sendUser')
                ->with('notPayOrder')
                ->orderBy('guo.created_at desc');
            if ($this->user_order_id) {
                $model->andWhere(['guo.id' => $this->user_order_id]);
            }
            $list = $model->page($pagination)
                ->asArray()->all();

            //状态
            foreach ($list as &$item) {
                $item['status'] = '';
                $item['status_num'] = 0;
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 0) {
                    $item['status'] = '已中奖';
                    $item['status_num'] = 1;
                }

                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1
                    && @$item['giftOrder'][0]['order']['is_confirm'] == 0 && @$item['giftOrder'][0]['order']['is_send'] == 1) {
                    $item['status'] = '待收货';
                    $item['status_num'] = 2;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 0 && @$item['giftOrder'][0]['is_refund'] == 1) {
                    $item['status'] = '超时';
                    $item['status_num'] = 3;
                }
                if ($item['is_turn'] == 1 && $item['is_turn'] > 0) {
                    $item['status'] = '已转赠';
                    $item['status_num'] = 4;
                }
                if ($item['is_win'] == 0 && $item['giftLog']['is_confirm'] == 1) {
                    $item['status'] = '未中奖';
                    $item['status_num'] = 5;
                }
                if ($item['giftLog']['is_confirm'] == 0) {
                    $item['status'] = '等待开奖';
                    $item['status_num'] = 6;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1 && @$item['giftOrder'][0]['order']['is_confirm'] == 1) {
                    $item['status'] = '已完成';
                    $item['status_num'] = 7;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1 && @$item['giftOrder'][0]['order']['is_send'] == 0) {
                    $item['status'] = '已兑换';
                    $item['status_num'] = 8;
                }

                //统一格式
                $item = $this->data($item);

                //判断礼包商品数量
                $item['is_big_gift'] = 0;
                if (@count($item['giftLog']['sendOrder']) > 1 || @count($item['giftLog']['sendOrder'][0]['detail']) > 1) {
                    $item['is_big_gift'] = 1;
                }
            }

            if ($this->user_order_id) {
                $template_message_captain = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                    'order_pay_tpl',
                    'order_cancel_tpl',
                    'order_send_tpl'
                ]);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'detail' => @$list[0],
                        'template_message_captain' => $template_message_captain,
                    ]
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch
        (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    //我收到的
    public function getMyList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            $model = GiftUserOrder::find()->alias('guo')
                ->andWhere([
                    'guo.mall_id' => \Yii::$app->mall->id,
                    'guo.is_delete' => 0,
                    'guo.user_id' => \Yii::$app->user->id,
                    'guo.is_win' => 1,
                ])->rightJoin(['gl' => GiftLog::tableName()], "gl.id = guo.gift_id and gl.type = 'direct_open'")
                ->with('giftLog.sendOrder.detail.goods.goodsWarehouse')
                ->with('giftOrder.order.store')
                ->with('giftOrder.order.detail.goods.goodsWarehouse')
                ->with('giftOrder.order.detail.orderRefund')
                ->with('giftOrder.order.detailExpress.expressRelation.orderDetail')
                ->with('giftOrder.goods.goodsWarehouse')
                ->with('giftOrder.goodsAttr')
                ->with('sendUser')
                ->with('notPayOrder')
                ->orderBy('guo.created_at desc');
            if ($this->user_order_id) {
                $model->andWhere(['guo.id' => $this->user_order_id]);
            }
            $list = $model->page($pagination)
                ->asArray()->all();

            //状态
            foreach ($list as &$item) {
                $item['status'] = '';
                $item['status_num'] = 0;
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 0) {
                    $item['status'] = '已抢到';
                    $item['status_num'] = 1;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1
                    && @$item['giftOrder'][0]['order']['is_confirm'] == 0 && @$item['giftOrder'][0]['order']['is_send'] == 1) {
                    $item['status'] = '待收货';
                    $item['status_num'] = 2;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 0 && @$item['giftOrder'][0]['is_refund'] == 1) {
                    $item['status'] = '超时';
                    $item['status_num'] = 3;
                }
                if ($item['is_turn'] == 1 && $item['is_turn'] > 0) {
                    $item['status'] = '已转赠';
                    $item['status_num'] = 4;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1 && @$item['giftOrder'][0]['order']['is_confirm'] == 1) {
                    $item['status'] = '已完成';
                    $item['status_num'] = 7;
                }
                if ($item['is_win'] == 1 && $item['is_turn'] == 0 && $item['is_receive'] == 1 && @$item['giftOrder'][0]['order']['is_send'] == 0) {
                    $item['status'] = '已兑换';
                    $item['status_num'] = 8;
                }

                //统一格式
                $item = $this->data($item);

                //判断礼包商品数量
                $item['is_big_gift'] = 0;
                if (@count($item['giftLog']['sendOrder']) > 1 || @count($item['giftLog']['sendOrder'][0]['detail']) > 1) {
                    $item['is_big_gift'] = 1;
                }

            }

            if ($this->user_order_id) {
                $template_message_captain = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                    'order_pay_tpl',
                    'order_cancel_tpl',
                    'order_send_tpl'
                ]);

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'detail' => @$list[0],
                        'template_message_captain' => $template_message_captain,

                    ]
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }


    protected function dataReturn($detail)
    {
        switch ($detail['type']) {
            case 'direct_open':
                //一人拿所有奖品
                if ($detail['open_type'] == 0) {
//                    if (count($detail['winUser']) > 0 && strtotime($detail['auto_refund_time']) < time()) {
                    if (count($detail['winUser']) > 0) {
                        $detail['status'] = '已完成';
                        $detail['status_num'] = 1;
                        $detail = $this->success($detail);
                    } elseif (strtotime($detail['auto_refund_time']) > time()) {
                        $detail['status'] = '等待领取';
                        $detail['status_num'] = 4;
                        $detail = $this->direct_open_wait($detail);
                    } elseif (count($detail['winUser']) == 0 && strtotime($detail['auto_refund_time']) < time()) {
                        $detail['status'] = '领取失败';
                        $detail['status_num'] = 2;
                        $detail = $this->fail($detail);
                    }
                } else {
                    if ((count($detail['winUser']) > 0 && strtotime($detail['auto_refund_time']) < time())
                        || (count($detail['winUser']) == $detail['num'])) {
                        $detail['status'] = '已完成';
                        $detail['status_num'] = 1;
                        $detail = $this->success($detail);
                    } elseif (count($detail['winUser']) <= $detail['num'] && strtotime($detail['auto_refund_time']) > time()) {
                        $detail['status'] = '等待领取';
                        $detail['status_num'] = 4;
                        $detail = $this->direct_open_wait($detail);
                    } elseif (count($detail['winUser']) == 0 && strtotime($detail['auto_refund_time']) < time()) {
                        $detail['status'] = '领取失败';
                        $detail['status_num'] = 2;
                        $detail = $this->fail($detail);
                    }
                }
                break;
            case 'time_open':
                if (count($detail['winUser']) > 0 && strtotime($detail['open_time']) < time()) {
                    $detail['status'] = '已完成';
                    $detail['status_num'] = 1;
                    $detail = $this->success($detail);
                } elseif (strtotime($detail['open_time']) > time()) {
                    $detail['status'] = '等待开奖';
                    $detail['status_num'] = 3;
                    $detail = $this->wait($detail);
                } elseif (count($detail['winUser']) == 0 && strtotime($detail['open_time']) < time()) {
                    $detail['status'] = '开奖失败';
                    $detail['status_num'] = 2;
                    $detail = $this->fail($detail);
                }
                break;
            case 'num_open':
//                if (count($detail['winUser']) >= $detail['open_num'] && strtotime($detail['auto_refund_time']) < time()) {
                if (count($detail['winUser']) >= $detail['open_num']) {
                    $detail['status'] = '已完成';
                    $detail['status_num'] = 1;
                    $detail = $this->success($detail);
                } elseif (count($detail['winUser']) < $detail['open_num'] && strtotime($detail['auto_refund_time']) > time()) {
                    $detail['status'] = '等待开奖';
                    $detail['status_num'] = 3;
                    $detail = $this->wait($detail);
                } elseif (count($detail['winUser']) < $detail['open_num'] && strtotime($detail['auto_refund_time']) < time()) {
                    $detail['status'] = '开奖失败';
                    $detail['status_num'] = 2;
                    $detail = $this->fail($detail);
                }
                break;
        }

        $detail['pay_time'] = $detail['sendOrder'][0]['pay_time'];
        //去除多余数据
        unset($detail['sendOrder']);
        unset($detail['winUser']);

        return $detail;
    }

    //已完成
    protected function success($detail)
    {
        $detail['success_list']['list'] = [];
        $detail['success_list']['total_price'] = 0;
        $detail['refund_list']['list'] = [];
        $detail['refund_list']['total_price'] = 0;
        foreach ($detail['sendOrder'] as $item) {
            foreach ($item['detail'] as $value) {
                $price = bcdiv($value['total_price'], $value['num']);
                if ($value['receive_num'] > 0) {
                    $detail['success_list']['list'][] = [
                        'name' => $value['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                        'goods_info' => $value['goods_info'],
                        'num' => (int)$value['receive_num'],
                        'total_price' => bcmul($price, $value['receive_num']),
                    ];
                    $detail['success_list']['total_price'] = bcadd($value['total_price'], $detail['success_list']['total_price']);
                }
                if (bcsub($value['num'], $value['receive_num']) > 0) {
                    $detail['refund_list']['list'][] = [
                        'name' => $value['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                        'goods_info' => $value['goods_info'],
                        'num' => (int)bcsub($value['num'], $value['receive_num']),
                        'total_price' => bcmul($price, bcsub($value['num'], $value['receive_num'])),
                    ];
                    $detail['refund_list']['total_price'] = bcadd($value['total_price'], $detail['refund_list']['total_price']);
                }
            }
            $detail['order_no'] = $item['order_no'];
        }
        return $detail;
    }

    //失败
    protected function fail($detail)
    {
        $detail['refund_list']['list'] = [];
        $detail['refund_list']['total_price'] = 0;
        foreach ($detail['sendOrder'] as $item) {
            foreach ($item['detail'] as $value) {
                $detail['refund_list']['list'][] = [
                    'name' => $value['goods']['goodsWarehouse']['name'],
                    'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                    'goods_info' => $value['goods_info'],
                    'num' => (int)$value['num'],
                    'total_price' => $value['total_price'],
                ];
                $detail['refund_list']['total_price'] = bcadd($value['total_price'], $detail['refund_list']['total_price']);
            }
            $detail['order_no'] = $item['order_no'];
        }
        return $detail;
    }

    //等待
    protected function wait($detail)
    {
        $detail['wait_list']['list'] = [];
        $detail['wait_list']['total_price'] = 0;
        foreach ($detail['sendOrder'] as $item) {
            foreach ($item['detail'] as $value) {
                $detail['wait_list']['list'][] = [
                    'name' => $value['goods']['goodsWarehouse']['name'],
                    'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                    'goods_info' => $value['goods_info'],
                    'num' => (int)$value['num'],
                    'total_price' => $value['total_price'],
                ];
                $detail['wait_list']['total_price'] = bcadd($value['total_price'], $detail['wait_list']['total_price']);
            }
            $detail['order_no'] = $item['order_no'];
        }
        return $detail;
    }

    //直接送礼——等待
    protected function direct_open_wait($detail)
    {
        $detail['convert_list']['list'] = [];
        $detail['convert_list']['total_price'] = 0;
        $detail['wait_list']['list'] = [];
        $detail['wait_list']['total_price'] = 0;
        foreach ($detail['sendOrder'] as $item) {
            foreach ($item['detail'] as $value) {
                $price = bcdiv($value['total_price'], $value['num']);
                if ($value['receive_num'] > 0) {
                    $detail['convert_list']['list'][] = [
                        'name' => $value['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                        'goods_info' => $value['goods_info'],
                        'num' => (int)$value['receive_num'],
                        'total_price' => bcmul($price, $value['receive_num']),
                    ];
                    $detail['convert_list']['total_price'] = bcadd($value['total_price'], $detail['convert_list']['total_price']);
                }
                if (bcsub($value['num'], $value['receive_num']) > 0) {
                    $detail['wait_list']['list'][] = [
                        'name' => $value['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $value['goods']['goodsWarehouse']['cover_pic'],
                        'goods_info' => $value['goods_info'],
                        'num' => (int)bcsub($value['num'], $value['receive_num']),
                        'total_price' => bcmul($price, bcsub($value['num'], $value['receive_num'])),
                    ];
                    $detail['wait_list']['total_price'] += bcmul($price, bcsub($value['num'], $value['receive_num']));
                }
            }
            $detail['order_no'] = $item['order_no'];
        }
        return $detail;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function data($item)
    {
        $item['detail'] = null;
        $item['order_no'] = null;
        if (!empty($item['giftOrder']) && count($item['giftOrder']) > 0) {
            $item['order_no'] = $item['giftOrder'][0]['order']['order_no'] ?? null;
            if (empty($item['giftOrder'][0]['order'])) {
                foreach ($item['giftOrder'] as $o) {
                    $goods_info = @(new Goods())->signToAttr($o['goodsAttr']['sign_id'], $o['goods']['attr_groups']) ?? [];
                    $attr_name = '';
                    if (!empty($goods_info)) {
                        foreach ($goods_info as $attr) {
                            $attr_name .= $attr['attr_group_name'] . ':' . $attr['attr_name'] . ' ';
                        }
                    }
                    $goods_info = ['goods_list' => $goods_info, 'goods_attr' => $o['goodsAttr']];
                    $item['detail'][] = [
                        'goods_id' => $o['goods_id'],
                        'goods_attr_id' => $o['goods_attr_id'],
                        'goods_info' => $goods_info,
                        'switch' => false,
                        'refund' => -1,
                        'orderRefund' => (object)array(),
                        'detail_id' => '',
                        'name' => $o['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $o['goods']['goodsWarehouse']['cover_pic'],
                        'num' => $o['num'],
                        'attr' => $attr_name,
                        'pic_url' => $o['goodsAttr']['pic_url'],
                        'is_convert' => ($o['goods']['status'] == 1 && $o['goods']['is_delete'] == 0) ? 1 : -1,
                        'is_confirm' => 0
                    ];
                }
            } else {
                foreach ($item['giftOrder'] as $d) {
                    foreach ($d['order']['detail'] as &$o) {
                        $goods_info = json_decode($o['goods_info'], true);
                        $attr_name = '';
                        if (!empty($goods_info['attr_list'])) {
                            foreach ($goods_info['attr_list'] as $attr) {
                                $attr_name .= $attr['attr_group_name'] . ':' . $attr['attr_name'] . ' ';
                            }
                        }
                        if (@count($o['orderRefund']) > 0) {
                            $bool = 1;
//                            if ($o['orderRefund']['status'] == 3) {
//                                $bool = 2;
//                            }
                            $o['orderRefund']['pic_list'] = json_decode($o['orderRefund']['pic_list'], true);
                            $refund = $o['orderRefund'];
                        } else {
                            $bool = -1;
                            if ($d['order']['is_send'] == 1) {
                                $bool = 0;
                            }
                            $refund = (object)array();
                        }
                        if (@$o['orderRefund']['is_confirm'] == 1 || $d['order']['is_sale'] == 1) {
                            $bool = -1;
                        }
                        $item['detail'][] = [
                            'goods_id' => $o['goods_id'],
                            'goods_attr_id' => $goods_info['goods_attr']['id'],
                            'goods_info' => $o['goods_info'],
                            'switch' => false,
                            'refund' => $bool,
                            'orderRefund' => $refund,
                            'detail_id' => $o['id'],
                            'name' => $o['goods']['goodsWarehouse']['name'],
                            'cover_pic' => $o['goods']['goodsWarehouse']['cover_pic'],
                            'num' => $o['num'],
                            'attr' => $attr_name,
                            'pic_url' => $goods_info['goods_attr']['pic_url'],
                            'is_convert' => ($o['goods']['status'] == 1 && $o['goods']['is_delete'] == 0) ? 1 : -1,
                            'is_confirm' => $d['order']['is_confirm']
                        ];
                    }
                }
            }
        } else {
            foreach ($item['giftLog']['sendOrder'] as $d) {
                foreach ($d['detail'] as $o) {
                    $goods_info = json_decode($o['goods_info'], true);
                    $attr_name = '';
                    if (!empty($goods_info['attr_list'])) {
                        foreach ($goods_info['attr_list'] as $attr) {
                            $attr_name .= $attr['attr_group_name'] . ':' . $attr['attr_name'] . ' ';
                        }
                    }
                    $item['detail'][] = [
                        'goods_id' => $o['goods_id'],
                        'goods_attr_id' => $o['goods_attr_id'],
                        'goods_info' => $o['goods_info'],
                        'switch' => false,
                        'refund' => -1,
                        'orderRefund' => (object)array(),
                        'detail_id' => '',
                        'name' => $o['goods']['goodsWarehouse']['name'],
                        'cover_pic' => $o['goods']['goodsWarehouse']['cover_pic'],
                        'num' => $o['num'],
                        'attr' => $attr_name,
                        'pic_url' => $goods_info['goods_attr']['pic_url'],
                        'is_convert' => ($o['goods']['status'] == 1 && $o['goods']['is_delete'] == 0) ? 1 : -1,
                        'is_confirm' => 0
                    ];
                }
            }
        }
        //二维数组去重
        $item['detail'] = array_unique($item['detail'], SORT_REGULAR);

        return $item;
    }
}