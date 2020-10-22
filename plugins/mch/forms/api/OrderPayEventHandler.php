<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\api;

use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonBuyPrompt;
use app\forms\common\template\TemplateSend;
use app\handlers\orderHandler\OrderPayedHandlerClass;
use app\plugins\mch\forms\common\MchOrderTemplate;
use app\plugins\mch\models\Mch;
use app\plugins\wxapp\models\WxappTemplate;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class OrderPayEventHandler extends OrderPayedHandlerClass
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->receiptPrint('pay')->sendSms()->saveResult()->sendMail()
            ->becomeJuniorByFirstPay()->becomeShare()->addShareOrder()
            ->sendTemplate()->sendBuyPrompt()->setGoods()->sendTemplateMsgToMch();
    }

    /**
     * @return $this
     * 短信发送--新订单通知
     */
    protected function sendSms()
    {
        try {
            if ($this->orderConfig->is_sms != 1) {
                throw new \Exception('未开启短信提醒');
            }
            $sms = new Sms();
            $smsConfig = CommonAppConfig::getSmsConfig(\Yii::$app->mchId);
            if ($smsConfig['status'] == 1 && $smsConfig['mobile_list']) {
                $sms->sendOrderMessage($smsConfig['mobile_list'], $this->event->order->order_no);
            }
        } catch (NoGatewayAvailableException $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 邮件发送--新订单通知
     */
    protected function sendMail()
    {
        // 发送邮件
        try {
            if ($this->orderConfig->is_mail != 1) {
                throw new \Exception('未开启邮件提醒');
            }
            $mailer = new SendMail();
            $mailer->mall = $this->mall;
            $mailer->mch_id = \Yii::$app->mchId;
            $mailer->order = $this->event->order;
            $mailer->orderPayMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 向小程序端发送购买提示消息
     */
    protected function sendBuyPrompt()
    {
        return $this;
        if (count($this->event->order->detail) > 0) {
            $details = $this->event->order->detail;
            $goods = $details[0]->goods;
            $goodsId = $goods->id;
            $goodsName = $goods->name;
        } else {
            $goodsId = 0;
            $goodsName = '';
        }
        try {
            $buy_data = new CommonBuyPrompt();
            $buy_data->nickname = $this->user->nickname;
            $buy_data->avatar = $this->user->userInfo->avatar;
            $buy_data->url = '/plugins/mch/goods/goods?id=' . $goodsId . '&mch_id=' . \Yii::$app->mchId;
            $buy_data->goods_name = $goodsName;
            $buy_data->set();
        } catch (\Exception $exception) {
            \Yii::error('首页购买提示失败: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function sendTemplateMsgToMch()
    {
        if ($this->event->order->mch_id == 0) {
            return false;
        }
        try {
            /** @var Mch $mch */
            $mch = Mch::find()->where(['id' => $this->event->order->mch_id])->with('user')->one();
            if (!$mch) {
                throw new \Exception('商户不存在,商户审核订阅消息发送失败');
            }

            if (!$mch->user) {
                throw new \Exception('用户不存在,商户审核订阅消息发送失败');
            }

            $mchOrderTemplate = new MchOrderTemplate([
                'order_no' => $this->event->order->order_no,
                'price' => $this->event->order->total_pay_price,
                'time' => $this->event->order->created_at,
                'remark' => $this->event->order->remark ? '备注:' . $this->event->order->remark : '有用户下单,请尽快处理'
            ]);

            $mchOrderTemplate->user = $mch->user;
            $mchOrderTemplate->page = 'plugins/mch/mch/order/order?mch_id=' . $this->event->order->mch_id;
            $res = $mchOrderTemplate->send();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }
}
