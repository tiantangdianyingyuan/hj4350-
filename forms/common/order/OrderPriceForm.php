<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order;

use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Model;
use app\models\ShareOrder;
use app\plugins\mch\models\Mch;

class OrderPriceForm extends Model
{
    public $order_id;
    public $order_detail_id;
    public $express_price;
    public $total_price;
    public $mch_id;

    public function rules()
    {
        return [
            [['order_id', 'order_detail_id', 'mch_id'], 'integer'],
            [['express_price', 'total_price'], 'string'],
        ];
    }

    //修改运费
    public function updateExpress()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $this->mch_id = $this->mch_id ?: \Yii::$app->user->identity->mch_id;
            $order = Order::findOne([
                'id' => $this->order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id,
            ]);

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->is_pay != 0) {
                throw new \Exception('订单已支付,不能修改价格');
            }

            if ($this->express_price < 0) {
                throw new \Exception('请输入正确的金额');
            }

            $order->express_price = doubleval($this->express_price);
            $order->total_pay_price = doubleval($order->express_price + $order->total_goods_price);
            if ($order->express_price < 0) {
                throw new \Exception('运费不能小于0');
            }
            if (!$order->save()) {
                throw new \Exception($this->getErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '运费修改成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    //修改价格
    public function updatePrice()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->mch_id = $this->mch_id ?: \Yii::$app->user->identity->mch_id;
            /** @var OrderDetail $orderDetail */
            $orderDetail = OrderDetail::find()->alias('od')->where([
                'od.id' => $this->order_detail_id,
                'od.is_delete' => 0
            ])->innerJoinWith(['order o' => function ($query) {
                $query->where([
                    'o.is_delete' => 0,
                    'o.mall_id' => \Yii::$app->mall->id,
                    'o.mch_id' => $this->mch_id,
                ]);
            }])->one();

            if (!$orderDetail) {
                throw new \Exception('订单不存在');
            }

            if ($orderDetail->order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($orderDetail->order->is_pay != 0) {
                throw new \Exception('订单已支付,不能修改价格');
            }

            if ($orderDetail->order->is_send == 1) {
                throw new \Exception('订单发货,不能修改价格');
            }

            if ($this->total_price < 0) {
                throw new \Exception('请输入正确的金额');
            }

            // 多商户
            if ($this->mch_id > 0) {
                // 多商户修改单价金额 不能低于总的分销金额 + 商户佣金
                $shareOrder = ShareOrder::findOne(['order_detail_id' => $orderDetail->id, 'is_delete' => 0]);
                $totalSharePrice = 0;
                if ($shareOrder) {
                    $totalSharePrice = $shareOrder->first_price + $shareOrder->second_price + $shareOrder->third_price;
                }
                $mch = Mch::findOne($this->mch_id);
                if (!$mch) {
                    throw new \Exception('商户不存在');
                }

                $deductPrice = $orderDetail->total_price * $mch->transfer_rate / 1000;
                $totalPrice = $totalSharePrice + $deductPrice;
                if ($this->total_price < $totalPrice) {
                    throw new \Exception('商品价格不能低于' . $totalPrice . '（此金额为分销佣金 + 商户手续费）');
                }
            }

            $oldBackPrice = $orderDetail->back_price;
            $backPrice = price_format($orderDetail->total_price + $oldBackPrice - $this->total_price);
            $orderDetail->back_price = $backPrice;
            $orderDetail->total_price = price_format($this->total_price);
            if (!$orderDetail->save()) {
                throw new \Exception($this->getErrorMsg($orderDetail));
            }
//            $totalBackPrice = OrderDetail::find()->select('SUM(back_price)')->where([
//                    'order_id' => $orderDetail->order_id,
//                    'is_delete' => 0
//                ])->scalar();
            $orderOldBackPrice = $orderDetail->order->back_price;
            $orderDetail->order->back_price = price_format($backPrice + ($orderDetail->order->back_price - $oldBackPrice));
            $totalGoodsPrice = $orderDetail->order->total_goods_price + $orderOldBackPrice - $orderDetail->order->back_price;
            $orderDetail->order->total_goods_price = price_format($totalGoodsPrice);
            $orderDetail->order->total_pay_price = price_format($orderDetail->order->express_price + $totalGoodsPrice);
            if ($orderDetail->total_price < 0) {
                throw new \Exception('价格不能小于0');
            }
            $res = $orderDetail->order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderDetail->order));
            }

            \Yii::$app->trigger(Order::EVENT_CHANGE_PRICE, new OrderEvent([
                'order' => $orderDetail->order
            ]));
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '价格修改成功'
            ];
        } catch (\Exception $e) {
            $t->rollback();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 更新订单总价
     */
    public function updateTotalPrice()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->mch_id = $this->mch_id ?: \Yii::$app->user->identity->mch_id;
            /** @var Order $order */
            $order = Order::find()->where([
                'id' => $this->order_id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $this->mch_id
            ])->one();
            if (!$order) {
                throw new \Exception('订单不存在');
            }
            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }
            if ($order->is_pay != 0) {
                throw new \Exception('订单已支付,不能修改价格');
            }

            if ($order->is_send == 1) {
                throw new \Exception('订单已发货,不能修改价格');
            }

            if ($this->total_price < 0 || $this->express_price < 0) {
                throw new \Exception('请输入正确的金额');
            }

            // 多商户
            if ($this->mch_id > 0) {
                // 多商户修改单价金额 不能低于总的分销金额 + 商户佣金
                /* @var ShareOrder[] $shareOrder */
                $shareOrder = ShareOrder::find()->where(['order_id' => $order->id, 'is_delete' => 0])->all();
                $totalSharePrice = 0;
                if ($shareOrder) {
                    foreach ($shareOrder as $item) {
                        $totalSharePrice += $item->first_price
                            + $item->second_price
                            + $item->third_price;
                    }
                }
                $mch = Mch::findOne($this->mch_id);
                if (!$mch) {
                    throw new \Exception('商户不存在');
                }

                $deductPrice = $order->total_pay_price * $mch->transfer_rate / 1000;
                $totalPrice = $totalSharePrice + $deductPrice;
                if (doubleval($this->total_price) + doubleval($this->express_price) < $totalPrice) {
                    throw new \Exception('商品价格不能低于' . $totalPrice . '（此金额为分销佣金 + 商户手续费）');
                }
            }

            $order->total_pay_price = $this->total_price + $this->express_price;
            $order->express_price = doubleval($this->express_price);
            if ($order->total_goods_price != $this->total_price) {
                $backPrice = floatval($order->total_goods_price + $order->back_price - $this->total_price);
                $order->back_price = $backPrice;
                $order->total_goods_price = $this->total_price;
                $type = true;
                if ($backPrice < 0) {
                    $type = false;
                }
                $backPrice = abs($backPrice);
                $resetPrice = $backPrice;
                $detailList = $order->detail;
                uasort($detailList, function ($a, $b) {
                    /* @var OrderDetail $a */
                    /* @var OrderDetail $b */
                    if ($b->total_price == $a->total_price) {
                        return 0;
                    }
                    return ($a->total_price < $b->total_price) ? -1 : 1;
                });
                $detailList = array_values($detailList);
                foreach ($detailList as $index => $detail) {
                    $goodsTotalPrice = floatval($detail->total_price) + floatval($detail->back_price);
                    if ($backPrice > 0) {
                        if ($resetPrice == 0) {
                            continue;
                        }
                        $goodsBackPrice = $goodsTotalPrice * $backPrice / (floatval($order->total_goods_price) + floatval($order->back_price));
                        $goodsBackPrice = price_format($goodsBackPrice, 'float');
                        if ($resetPrice < $goodsBackPrice || ($index == count($detailList) - 1 && $resetPrice > 0)) {
                            $goodsBackPrice = $resetPrice;
                        } else {
                            $resetPrice -= min($goodsBackPrice, $resetPrice);
                        }
                        if (!$type) {
                            $goodsBackPrice = 0 - $goodsBackPrice;
                        }
                    } else {
                        $goodsBackPrice = 0;
                    }
                    $detail->back_price = $goodsBackPrice;
                    $detail->total_price = price_format($goodsTotalPrice - $goodsBackPrice);
                    if ($detail->total_price < 0) {
                        throw new \Exception('价格不能小于0');
                    }
                    if (!$detail->save()) {
                        throw new \Exception($this->getErrorMsg($detail));
                    }
                }
            }
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            \Yii::$app->trigger(Order::EVENT_CHANGE_PRICE, new OrderEvent([
                'order' => $order
            ]));
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '价格更新成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
