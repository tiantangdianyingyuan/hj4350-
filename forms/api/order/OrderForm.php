<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\order;

use Overtrue\EasySms\Message;
use app\core\mail\SendMail;
use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\forms\api\goods\MallGoods;
use app\forms\api\order\CityServiceMapForm;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonSms;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\order\CommonOrder;
use app\forms\common\order\CommonOrderList;
use app\forms\common\template\TemplateList;
use app\forms\mall\city_service\CityServiceForm;
use app\models\CityService;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderDetailExpress;
use app\models\OrderDetailExpressRelation;
use app\models\OrderRefund;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderForm extends Model
{
    public $page;
    public $limit;
    public $status;
    public $id; // 订单ID
    public $cancel_data;
    public $keyword;
    public $dateArr;
    public $express_id;

    private $isComment;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'id', 'express_id'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            ['status', 'default', 'value' => 0],
            [['cancel_data', 'keyword'], 'string'],
            [['keyword', 'dateArr'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        // 售后订单列表
        if ($this->status == 5) {
            return $this->getRefundOrderList();
        }

        $form = new CommonOrderList();
        $form->user_id = \Yii::$app->user->id;
        $form->status = $this->status;
        $form->is_detail = 1;
        $form->is_goods = 1;
        $form->is_comment = 1;
        $form->keyword = $this->keyword;
        $form->dateArr = json_decode($this->dateArr, true);
        $form->page = $this->page;
        $form->is_recycle = 0;
        $form->relations = ['detailExpress.expressRelation.orderDetail', 'detail.expressRelation', 'detailExpressRelation.orderExpress'];
        $form->add_where = [
            'or',
            [
                'o.sign' => 'scan_code_pay',
                'o.is_pay' => 1,
                'o.is_sale' => 1,
                'o.is_confirm' => 1,
            ],
            [
                'and',
                ['o.sign' => 'gift',],
                ['!=', 'o.auto_cancel_time', ''],
            ],
            [
                'o.sign' => 'gift',
                'o.is_pay' => 1,
                'o.is_sale' => 1,
                'o.auto_cancel_time' => null,
            ],
            ['not in', 'o.sign', ['scan_code_pay', 'gift']],
        ];
        $list = $form->search();

        $newList = [];
        $order = new Order();
        $this->isComment = (new Mall())->getMallSettingOne('is_comment');
        /* @var Order[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['comments'] = $item->comments ? ArrayHelper::toArray($item->comments) : [];
            $newItem['detail'] = $item->detail ? ArrayHelper::toArray($item->detail) : [];
            $newItem['cancel_data'] = $item->cancel_data ? json_decode($item->cancel_data, true) : [];
            $newItem['status_text'] = $order->orderStatusText($item);
            $priceList = [];
            foreach ($item->detail as $key => $orderDetail) {
                $goodsInfo = MallGoods::getGoodsData($orderDetail);
                $newItem['detail'][$key]['goods_info'] = $goodsInfo;
                $priceList[] = [
                    'label' => '小计',
                    'value' => $orderDetail['total_price'],
                ];
            }

            // 兼容发货方式
            $newItem['is_offline'] = $item->send_type;

            $detailExpressRelation = [];
            foreach ($item->detailExpressRelation as $der) {
                $newDerItem = ArrayHelper::toArray($der);
                $newDerItem['orderExpress'] = $der->orderExpress ? ArrayHelper::toArray($der->orderExpress) : [];
                $detailExpressRelation[] = $newDerItem;
            }

            $newDetailExpress = [];
            /** @var OrderDetailExpress $detailExpress */
            foreach ($item->detailExpress as $detailExpress) {
                $newDeItem = ArrayHelper::toArray($detailExpress);
                $newExpressRelation = [];
                /** @var OrderDetailExpressRelation $erItem */
                foreach ($detailExpress->expressRelation as $erItem) {
                    $newErItem = ArrayHelper::toArray($erItem);
                    $newErItem['orderDetail'] = $erItem->orderDetail ? ArrayHelper::toArray($erItem->orderDetail) : [];
                    $newErItem['orderDetail']['goods_info'] = $erItem->orderDetail ? \Yii::$app->serializer->decode($erItem->orderDetail->goods_info) : [];
                    $newExpressRelation[] = $newErItem;
                }
                $newDeItem['expressRelation'] = $newExpressRelation;
                $newDetailExpress[] = $newDeItem;
            }
            $newItem['detailExpress'] = $newDetailExpress;
            $newItem['action_status'] = $this->getActionStatus($item);
            $newItem['plugin_data'] = $item->getPluginData($item, $priceList);

            $newItem['der_info'] = $detailExpressRelation;
            $newList[] = $newItem;
        }

        $tpl = ['order_pay_tpl', 'order_cancel_tpl', 'order_send_tpl'];
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $form->pagination,
                'template_message' => TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $tpl),
            ],
        ];
    }

    private function getActionStatus($order)
    {
        $actionStatus = [
            'is_show_comment' => 0, // 是否显示评论
        ];

        if ($order->is_confirm == 1 && !$order->comments) {
            $actionStatus['is_show_comment'] = 1;
        }

        if ($order->sign) {
            $PluginClass = 'app\\plugins\\' . $order->sign . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (class_exists($PluginClass)) {
                $object = new $PluginClass();
                if (method_exists($object, 'getOrderAction')) {
                    $actionStatus = $object->getOrderAction($actionStatus, $order);
                }
            }
        }
        // 商城是否开始评价功能
        if ($this->isComment != 1) {
            $actionStatus['is_show_comment'] = 0;
        }

        return $actionStatus;
    }

    /**
     * 售后订单列表
     * @return array
     */
    public function getRefundOrderList()
    {
        try {
            $list = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])
                ->with(['detail.goods.goodsWarehouse'])
                ->page($pagination)
                ->orderBy('created_at DESC')
                ->all();

            $orderRefund = new OrderRefund();
            $newList = [];
            /** @var OrderRefund $item */
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                $newItem['status_text'] = $orderRefund->statusText($item);
                $goodsInfo = MallGoods::getGoodsData($item->detail);
                $newItem['detail'][] = ['goods_info' => $goodsInfo];
                $newItem = array_merge($newItem, $item->checkAfterRefund($item));
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    public function orderConfirm()
    {
        try {
            /* @var Order $order */
            $order = Order::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->one();

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,无法确认收货');
            }

            CommonOrder::getCommonOrder($order->sign)->confirm($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    public function orderCancel()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            /* @var Order $order */
            $order = Order::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'id' => $this->id,
                'is_send' => 0,
                'is_sale' => 0,
                'is_confirm' => 0,
            ])->with(['userCards' => function ($query) {
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

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,无法取消');
            }

            if (count($order->userCards) > 0) {
                throw new \Exception('订单赠送的卡券已使用,该订单无法取消');
            }

            if ($order->cancel_status != 0) {
                throw new \Exception('该订单已处理,请刷新页面');
            }

            // 未支付订单直接取消 无需后台审核 货到付款订单没有直接取消，只能申请取消
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                $order->cancel_status = 1;
                $order->cancel_time = mysql_timestamp();
            } else {
                // 待后台审核
                $order->cancel_status = 2;
                $order->cancel_data = $this->cancel_data;
            }
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            if ($order->cancel_status == 1) {
                \Yii::$app->trigger(Order::EVENT_CANCELED, new OrderEvent(['order' => $order]));
            }
            $t->commit();

            // 发送短信
            $this->sendRefundSms($order);
            // 发送邮件
            $this->sendMail($order);
            //公众号模版消息
            $this->sendMpTpl($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $order->cancel_status == 1 ? '取消成功' : '待后台审核',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function checkData()
    {
        if (isset($this->cancel_data)) {
            $cancelData = json_decode($this->cancel_data, true);
            if (mb_strlen($cancelData['remark']) > 200) {
                throw new \Exception('备注信息最多输入200个字');
            }
        }
    }

    /**
     * 发送邮件
     * @param $order
     */
    private function sendMail($order)
    {
        try {
            $mailer = new SendMail();
            $mailer->mall = \Yii::$app->mall;
            $mailer->order = $order;
            $mailer->refundMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送:' . $exception->getMessage());
        }
    }

    /**
     * 发送公众号消息
     * @param Order $order
     */
    private function sendMpTpl($order)
    {
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'cancelOrderTpl';
            $tplMsg->params = [
                'order_no' => $order->order_no,
                'price' => $order->total_goods_price,
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
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
                'data' => $data,
            ]);
            $sms = \Yii::$app->sms->module('mall');
            foreach ($smsConfig['mobile_list'] as $mobile) {
                $sms->send($mobile, $message);
            }
        } catch (\Exception $exception) {
            \Yii::error('生成售后订单：' . $exception->getMessage());
        }
    }

    /**
     * 用户撤销申请退款（未发货）
     */
    public function cancelApply()
    {
        try {
            /** @var Order $order */
            $order = Order::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->cancel_status != 2) {
                throw new \Exception($order->cancel_status == 1 ? '商家已同意退款申请' : '商家已拒绝退款申请');
            }

            $order->cancel_status = 0;
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '撤销成功',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function cityMap()
    {
        try {
            $cityServiceMap = new CityServiceMapForm();
            $cityServiceMap->express_id = $this->express_id;
            $data = $cityServiceMap->cityMap();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'express_data' => $data,
                ],
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}
