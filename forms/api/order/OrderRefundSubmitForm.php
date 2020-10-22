<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use app\core\mail\SendMail;
use app\core\response\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonSms;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use Overtrue\EasySms\Message;
use yii\db\Query;

class OrderRefundSubmitForm extends Model
{
    public $id; // 订单详情ID
    public $type;
    public $refund_price;
    public $remark;
    public $pic_list;
    public $mobile;
    public $goods_status;
    public $cause;

    public function rules()
    {
        return [
            [['id', 'type', 'remark', 'pic_list', 'refund_price'], 'required'],
            [['id', 'type'], 'integer'],
            [['remark', 'refund_price', 'cause', 'goods_status', 'mobile'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'remark' => '备注',
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkIsRefund();
            $this->checkData();
            /** @var OrderDetail $orderDetail */
            $orderDetail = OrderDetail::find()->where([
                'id' => $this->id,
            ])->with(['order', 'userCards' => function ($query) {
                /** @var Query $query */
                $query->andWhere(
                    [
                        'or',
                        ['>', 'use_number', 0],
                        ['is_use' => 1],
                        ['>', 'receive_id', 0]
                    ]
                );
            }])->one();

            if (!$orderDetail) {
                throw new \Exception('订单不存在');
            }
            if ($orderDetail->order->is_sale == 1) {
                throw new \Exception('订单已过售后时间,无法申请售后');
            }
            if ($orderDetail->order->status == 0) {
                throw new \Exception('订单进行中,无法申请售后');
            }
            //预售尾款订单，售后可退金额为尾款+定金
            $advance_price = 0;
            if ($orderDetail->order->sign == 'advance') {
                //判断是否存在插件，是否有插件权限
                $bool           = false;
                $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo); //取商城所属账户权限
                if (!is_array($permission_arr) && $permission_arr) {
                    $bool = true;
                } else {
                    foreach ($permission_arr as $value) {
                        if ($value == 'advance') {
                            $bool = true;
                            break;
                        }
                    }
                }
                if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                    $plugin = \Yii::$app->plugin->getPlugin('advance');
                    /* @var AdvanceOrder $advance_info */
                    $advance_info = $plugin->getAdvance($orderDetail['order']['id'], $orderDetail['order']['order_no']);
                    if (!empty($advance_info)) {
                        $advance_price += bcmul($advance_info->goods_num, $advance_info->deposit);
                    }
                }
            }
            // 退款金额不能大于商品单价

            if (($this->type == 1 || $this->type == 3) && price_format($this->refund_price) > price_format($orderDetail->total_price + $advance_price)) {
                throw new \Exception('最多可退款金额￥' . price_format($orderDetail->total_price + $advance_price));
            }
            // 退款金额需去除运费
            $realityPrice = $orderDetail->order->total_pay_price - $orderDetail->order->express_price ?: 0;
            if (($this->type == 1 || $this->type == 3) && price_format($this->refund_price) > price_format($realityPrice + $advance_price)) {
                throw new \Exception('最多可退款金额￥' . price_format($realityPrice + $advance_price));
            }

            if (count($orderDetail->userCards) > 0 && ($this->type == 1 || $this->type == 3)) {
                throw new \Exception('商品赠送的卡券已使用,该商品无法申请退货');
            }

            // 生成售后订单
            $orderRefund                  = new OrderRefund();
            $orderRefund->mall_id         = \Yii::$app->mall->id;
            $orderRefund->mch_id          = $orderDetail->order->mch_id;
            $orderRefund->user_id         = \Yii::$app->user->id;
            $orderRefund->order_id        = $orderDetail->order_id;
            $orderRefund->order_detail_id = $this->id;
            $orderRefund->order_no        = Order::getOrderNo('RE');
            $orderRefund->type            = $this->type;
            $orderRefund->refund_price    = $this->refund_price;
            $orderRefund->remark          = $this->remark;
            $orderRefund->pic_list        = $this->pic_list;
            $orderRefund->is_refund       = 0; //数据库默认为2,所以这里要指定为0
            $orderRefund->mobile          = $this->mobile ?: '';
            $orderRefund->refund_data     = json_encode([
                'goods_status' => $this->goods_status ?: '',
                'cause'        => $this->cause ?: $this->cause,
            ]);
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }

            // 更新订单详情售后状态
            $orderDetail->refund_status = 1;
            if (!$orderDetail->save()) {
                throw new \Exception($this->getErrorMsg($orderDetail));
            }

            // 更新订单售后状态
            // TODO 如果一个订单多个商品第一个商品就申请了售后，统计会不会有什么问题
            if ($orderDetail->order->sale_status == 0) {
                $orderDetail->order->sale_status = 1;
                if (!$orderDetail->order->save()) {
                    throw new \Exception($this->getErrorMsg($orderDetail->order));
                }
            }
            $t->commit();
            $this->sendRefundSms($orderDetail->order);
            $this->sendMail($orderRefund->type, $orderDetail->order);
            $this->sendMpTplMsg($orderDetail->order);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '提交成功, 请等待商家处理',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code'  => ApiCode::CODE_ERROR,
                'msg'   => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    /**
     * 发送邮件
     * @param $orderRefundType
     * @param $order
     */
    private function sendMail($orderRefundType, $order)
    {
        if ($orderRefundType == 1) {
            // 发送邮件
            try {
                $mailer        = new SendMail();
                $mailer->mall  = \Yii::$app->mall;
                $mailer->order = $order;
                $mailer->refundMsg();
            } catch (\Exception $exception) {
                \Yii::error('邮件发送: ');
                \Yii::error($exception);
            }
        }
    }

    /**
     * 发送公众号消息
     * @param Order $order
     */
    private function sendMpTplMsg($order)
    {
        //公众号消息
        try {
            $tplMsg         = new MpTplMsgSend();
            $tplMsg->method = 'saleOrderTpl';
            $tplMsg->params = [
                'order_no' => $order->order_no,
                'status'   => ($order->is_send == 0) ? '未发货' : ($order->is_confirm == 0 ? '未收货' : '已收货'),
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * 每个订单商品被拒绝售后 后可再申请一次售后, 检测该订单商品是否已经售后过.
     */
    private function checkIsRefund()
    {
        $orderRefund = OrderRefund::find()->where([
            'mall_id'         => \Yii::$app->mall->id,
            'order_detail_id' => $this->id,
            'is_delete'       => 0,
        ])->one();


        $orderRefundList = OrderRefund::find()->where([
            'mall_id'         => \Yii::$app->mall->id,
            'order_detail_id' => $this->id,
            'is_delete'       => 0,
        ])->all();

        if (($orderRefund && $orderRefund->status !=3) || count($orderRefundList) >= 2) {
            throw new \Exception('该订单已生成售后订单,无需重复申请');
        }
    }

    /**
     * 发送短信提醒
     * @return array
     */
    private function sendRefundSms($order)
    {
        try {
            $smsConfig = CommonAppConfig::getSmsConfig($order->mch_id);
            if ($smsConfig['status'] != 1) {
                throw new \Exception('短信功能未开启');
            }
            if (!is_array($smsConfig['mobile_list']) || count($smsConfig['mobile_list']) <= 0) {
                throw new \Exception('接收短信手机号不正确');
            }
            $setting = CommonSms::getCommon()->getSetting();
            if (!(isset($smsConfig['order_refund'])
                && isset($smsConfig['order_refund']['template_id'])
                && $smsConfig['order_refund']['template_id'])) {
                throw new \Exception($setting['order_refund']['title'] . '模板ID未设置');
            }
            $data = [];
            foreach ($setting['order_refund']['variable'] as $value) {
                $data[$smsConfig['order_refund'][$value['key']]] = '89757';
            }
            $message = new Message([
                'template' => $smsConfig['order_refund']['template_id'],
                'data'     => $data,
            ]);
            $sms = \Yii::$app->sms->module('mall');
            foreach ($smsConfig['mobile_list'] as $mobile) {
                $sms->send($mobile, $message);
            }
        } catch (\Exception $exception) {
            \Yii::error('生成售后订单：' . $exception->getMessage());
        }
    }

    private function checkData()
    {
        if (mb_strlen($this->remark) > 200) {
            throw new \Exception("备注最多输入200个字");
        }

        if (mb_strlen($this->cause) > 200) {
            throw new \Exception("换货原因最多输入200个字");
        }

        if (($this->type == 1 || $this->type == 3) && $this->refund_price < 0) {
            throw new \Exception('退款金额不能小于0');
        }
    }
}
