<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;


use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\forms\common\card\CommonSend;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonBuyPrompt;
use app\forms\common\coupon\CommonCouponAutoSend;
use app\forms\common\coupon\CommonCouponGoodsSend;
use app\forms\common\goods\CommonGoods;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\share\CommonShare;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\CouponAutoSend;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderPayResult;
use app\models\User;
use app\models\UserCard;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

/**
 * @property User $user
 */
abstract class BaseOrderPayedHandler extends BaseOrderHandler
{
    public $user;

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        $cardList = $this->sendCard();
        $userCouponList = $this->sendCoupon();
        $userCouponList = array_merge($userCouponList, $this->sendCouponByGoods());
        $data = [
            'card_list' => $cardList,
            'user_coupon_list' => $userCouponList,
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    /**
     * @return array
     * 向用户发送商品卡券
     */
    protected function sendCard()
    {
        try {
            $cardSendForm = new CommonSend();
            $cardSendForm->mall_id = \Yii::$app->mall->id;
            $cardSendForm->user_id = $this->event->order->user_id;
            $cardSendForm->order_id = $this->event->order->id;
            /** @var UserCard[] $userCardList */
            $userCardList = $cardSendForm->save();
            $cardList = [];
            foreach ($userCardList as $userCard) {
                $cardList[] = $userCard->attributes;
            }
        } catch (\Exception $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * @return array
     * 向用户发送优惠券（自动发送方案--订单支付成功发送优惠券）
     */
    protected function sendCoupon()
    {
        try {
            $couponSendForm = new CommonCouponAutoSend();
            $couponSendForm->event = CouponAutoSend::PAY;
            $couponSendForm->user = $this->user;
            $couponSendForm->mall = $this->mall;
            $userCouponList = $couponSendForm->send();
        } catch (\Exception $exception) {
            \Yii::error('优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    /**
     * @return array
     * 向用户发送优惠券（购买商品赠送--订单支付成功发送优惠券）
     */
    protected function sendCouponByGoods()
    {
        try {
            $couponSendForm = new CommonCouponGoodsSend();
            $couponSendForm->user = $this->user;
            $couponSendForm->mall = $this->mall;
            $couponSendForm->order_id = $this->event->order->id;
            $userCouponList = $couponSendForm->send();
            \Yii::warning('购买商品赠送优惠券发放数据');
            \Yii::warning($userCouponList);
        } catch (\Exception $exception) {
            \Yii::error('商品赠送优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
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
            $smsConfig = CommonAppConfig::getSmsConfig();
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
            $mailer->order = $this->event->order;
            $mailer->orderPayMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 首次付款成为下级
     */
    protected function becomeJuniorByFirstPay()
    {
        try {
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->user = $this->user;
            $commonShare->bindParent($this->user->userInfo->temp_parent_id, 3);
        } catch (\Exception $exception) {
            \Yii::error('首次付款成为下级：' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 下单成为分销商
     */
    protected function becomeShare()
    {
        try {
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->becomeShareByPayed($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('下单成为分销商: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过小程序模板消息发送给用户支付成功通知
     */
    protected function sendTemplate()
    {
        try {
            $template = new Tplmsg();
            $template->orderPayMsg($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过公众号向商家发送公众号消息
     */
    protected function sendMpTemplate()
    {
        $goodsName = '';
        foreach ($this->event->order->detail as $detail) {
            $goodsName .= $detail->goods->name;
        }
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'newOrderTpl';
            $tplMsg->params = [
                'sign' => $this->event->order->sign,
                'goods' => $goodsName,
                'time' => date('Y-m-d H:i:s'),
                'user' => $this->user->nickname,
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 向小程序端发送购买提示消息
     */
    protected function sendBuyPrompt()
    {
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
            $buy_data->url = '/pages/goods/goods/id=' . $goodsId;
            $buy_data->goods_name = $goodsName;
            $buy_data->set();
        } catch (\Exception $exception) {
            \Yii::error('首页购买提示失败: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function setGoods()
    {
        try {
            CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'add');
            CommonGoods::getCommon()->setGoodsSales($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('商品支付信息设置');
            \Yii::error($exception);
        }
        return $this;
    }
}
