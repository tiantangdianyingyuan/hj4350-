<?php

namespace app\forms\common\template\tplmsg;

use app\forms\common\template\TemplateSend;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpress;
use app\models\OrderRefund;
use app\models\User;
use app\plugins\advance\models\AdvanceOrder;
use yii\db\Exception;

class Tplmsg extends Model
{
    public $user;
    public $page = 'pages/index/index';
    public $remark = '';

    /**
     * @param OrderRefund $orderRefund
     * @param double $refund_price 退款金额
     * @param string $remark 备注
     * @return array
     * @throws \Exception
     * 发送退款模板消息
     */
    public function orderRefundMsg(OrderRefund $orderRefund, $refund_price, $remark)
    {
        try {
            $data = [
                'keyword1' => [
                    'value' => $orderRefund->order->order_no,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $orderRefund->detail->goods->name,
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $refund_price,
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $remark,
                    'color' => '#333333',
                ],
            ];
            $this->user = $orderRefund->order->user;
            $this->page = 'pages/order/refund/index';

            return $this->send($data, 'order_refund_tpl');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param array $orderRefund
     * @param double $refund_price
     * @param string $remark
     * @return array
     * @throws \Exception
     * 礼物退款模版消息
     */
    public function giftOrderRefundMsg($orderRefund, $refund_price, $remark)
    {
        try {
            $data = [
                'keyword1' => [
                    'value' => $orderRefund['order_no'],
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $orderRefund['name'],
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $refund_price,
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $remark,
                    'color' => '#333333',
                ],
            ];
            $this->user = $orderRefund['user'];
//            $this->page = 'pages/order/index/index?status=4';

            return $this->send($data, 'order_refund_tpl');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 订单核销模板消息
     * @param Order $order [description]
     * @param  [type] $remark 说明
     * @return array
     * @throws \Exception
     */
    public function orderClerkTplMsg(Order $order, $remark)
    {
        try {
            //TODO 多商户
            $data = [
                'keyword1' => [
                    'value' => $order->order_no,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $remark,
                    'color' => '#333333',
                ],
            ];
            $this->user = $order->user;
            $this->page = 'pages/order/index/index?status=3';
            return $this->send($data, 'order_send_tpl');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 订单发货通知
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    public function orderSendMsg(Order $order)
    {
        try {
            $goods_name = '';
            foreach ($order->detail as $item) {
                $goods_name .= $item['goods']['name'];
            }

            $express = '';
            $expressNo = '';
            $merchantRemark = '';
            /** @var OrderDetailExpress $orderDetailExpress */
            $orderDetailExpress = OrderDetailExpress::find()->where(['order_id' => $order->id])
                ->orderBy(['created_at' => SORT_DESC])->one();
            if ($orderDetailExpress) {
                if ($orderDetailExpress->send_type == 1) {
                    $express = $orderDetailExpress->express;
                    $expressNo = $orderDetailExpress->express_no;
                    $merchantRemark = $orderDetailExpress->merchant_remark;
                } else {
                    $merchantRemark = $orderDetailExpress->express_content;
                }
            }

            $data = [
                'keyword1' => [
                    'value' => $goods_name,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $express ? $express : '商家自己发货',
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $expressNo ? $expressNo : '-',
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $merchantRemark ? $merchantRemark : '商品已发货，注意查收！',
                    'color' => '#333333',
                ],
            ];
            $this->user = $order->user;
            $this->page = 'pages/order/index/index?status=3';
            return $this->send($data, 'order_send_tpl');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $data
     * @param $templateTpl
     * @return array
     * @throws \Exception
     * 模板消息发送接口
     */
    protected function send($data, $templateTpl)
    {
        $template = new TemplateSend();
        $template->user = $this->user;
        $template->page = $this->page;
        $template->data = $data;
        $template->templateTpl = $templateTpl;
        return $template->sendTemplate();
    }

    /**
     * @param Order $order
     * @return array
     * @throws Exception
     * 订单支付
     */
    public function orderPayMsg($order)
    {
        try {
            $goodsName = '';
            foreach ($order->detail as $orderDetail) {
                $goodsName .= $orderDetail->goods->name;
            }
            $data = [
                'keyword1' => [
                    'value' => $order->order_no,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $order->pay_time,
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $order->total_pay_price,
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $goodsName,
                    'color' => '#333333',
                ],
            ];
            $this->user = $order->user;
            $this->page = 'pages/order/index/index';
            return $this->send($data, 'order_pay_tpl');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return array
     * @throws Exception
     * 订单取消
     */
    public function orderCancelMsg($order)
    {
        try {
            $goodsName = '';
            foreach ($order->detail as $orderDetail) {
                $goodsName .= $orderDetail->goods->name;
            }
            $remark = '';
            $this->page = 'pages/order/index/index?status=2';
            if ($order->cancel_status == 1) {
                $remark = '商家同意取消';
            }
            if ($order->cancel_status == 0) {
                $remark = '商家拒绝取消';
            }

            $orderCancelTemplate = new OrderCancelTemplate([
                'goodsName' => $goodsName,
                'order_no' => $order->order_no,
                'price' => $order->total_pay_price,
                'remark' => $remark . ($order->words ? ',' . $order->words : ''),
            ]);

            $orderCancelTemplate->user = $order->user;
            $orderCancelTemplate->page = $this->page;
            $orderCancelTemplate->send();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $order
     * @param $price
     * @return array
     * @throws Exception
     * 定金订单取消
     */
    public function depositOrderCancelMsg($order, $price)
    {
        /**
         * @var AdvanceOrder $order
         */
        try {
            $goodsName = '预售退款';
            $remark = '商家取消订单，定金退回';
            $money = $price ?? $order->deposit * $order->goods_num;
            $data = [
                'keyword1' => [
                    'value' => $goodsName,
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $order->advance_no,
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => price_format($money),
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => $remark,
                    'color' => '#333333',
                ],
            ];
            $this->user = $order->user;
            return $this->send($data, 'order_cancel_tpl');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $arr
     * @return array
     * @throws Exception
     * 礼物开奖
     */
    public function giftConvert($arr)
    {
        try {
            $data = [
                'keyword1' => [
                    'value' => '送礼物' . $arr['title'],
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $arr['name'],
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $arr['remark'],
                    'color' => '#333333',
                ],
            ];
            $this->user = $arr['user'];
            return $this->send($data, 'gift_convert');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $arr
     * @return array
     * @throws Exception
     * 礼物领取人未填地址，通知送礼人
     */
    public function giftFormUser($arr)
    {
        try {
            $data = [
                'keyword1' => [
                    'value' => $arr['order_no'],
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $arr['name'],
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => '礼物超时失效',
                    'color' => '#333333',
                ],
            ];
            $this->user = $arr['user'];
            return $this->send($data, 'gift_form_user');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $arr
     * @return array
     * @throws Exception
     * 礼物领取人未填地址，通知收礼人
     */
    public function giftToUser($arr)
    {
        try {
            $data = [

                'keyword1' => [
                    'value' => $arr['order_no'],
                    'color' => '#333333',
                ],
                'keyword2' => [
                    'value' => $arr['name'],
                    'color' => '#333333',
                ],
                'keyword3' => [
                    'value' => $arr['time'],
                    'color' => '#333333',
                ],
                'keyword4' => [
                    'value' => '礼物超时失效',
                    'color' => '#333333',
                ],

            ];
            $this->user = $arr['user'];
            return $this->send($data, 'gift_to_user');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
