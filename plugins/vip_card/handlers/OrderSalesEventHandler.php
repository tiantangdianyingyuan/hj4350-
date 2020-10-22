<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/21
 * Time: 15:49
 */

namespace app\plugins\vip_card\handlers;

use app\handlers\orderHandler\BaseOrderSalesHandler;
use app\plugins\vip_card\jobs\VipCardRemindJob;
use app\plugins\vip_card\models\VipCardUser;

class OrderSalesEventHandler extends BaseOrderSalesHandler
{
    /**@var VipCardUser $vipUser**/
    private $vipUser;

    protected function action()
    {
        \Yii::warning('过售后事件开始');
        // 发放佣金
        $res = $this->giveShareMoney();
        // 发放积分
        $this->giveIntegral();
        // 消费升级会员等级
        $this->level();
        $this->editUserVipCard();
        $this->giveBalance();
        $this->remind();
    }

    protected function giveIntegral()
    {
        try {
            $sendData = $this->getSend();

            if ($sendData['send_integral_num'] > 0) {
                \Yii::$app->currency->setUser($this->user)->integral
                    ->add((int)$sendData['send_integral_num'], ($sendData['main']['name'] ?? '超级会员卡') . '赠送积分');
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error('超级会员卡积分赠送失败'. $e->getMessage());
            return false;
        }
    }

    protected function giveBalance()
    {
        try {
            $sendData = $this->getSend();

            if ($sendData['send_balance'] > 0) {
                \Yii::$app->currency->setUser($this->user)->balance
                    ->add((float)$sendData['send_balance'], ($sendData['main']['name'] ?? '超级会员卡') . '赠送余额');
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error('超级会员卡余额赠送失败'. $e->getMessage());
            return false;
        }
    }

    private function getSend()
    {
        $detail = $this->event->order->detail[0];
        $goodsInfo = \Yii::$app->serializer->decode($detail['goods_info']);
        return $goodsInfo['rules_data'];
    }

    private function editUserVipCard()
    {
        $user = VipCardUser::find()->where(['mall_id'=>$this->order->mall_id,'user_id'=>$this->order->user_id, 'is_delete' => 0])->one();
        $sendData = $this->getSend();
        if (!$user) {
            $user = new VipCardUser();
        }
        $user->mall_id = $this->order->mall_id;
        $user->main_id = $sendData['main']['id'];
        $user->detail_id = $this->getSend()['id'];
        $user->user_id = $this->order->user_id;
        $user->image_name = $sendData['name'];
        $user->image_main_name = $sendData['main']['name'];
        $user->image_discount = $sendData['main']['discount'];
        $user->image_is_free_delivery = $sendData['main']['is_free_delivery'];
        $user->image_type = $sendData['main']['type'];
        $user->image_type_info = $sendData['main']['type_info'];

        $allSend['send_integral_num'] = $sendData['send_integral_num'];
        $allSend['send_balance'] = $sendData['send_balance'];
        $allSend['cards'] = $sendData['cards'];
        $allSend['coupons'] = $sendData['coupons'];
        $user->all_send = json_encode($allSend);

        if ($user->isNewRecord) {
            $user->start_time = date('Y-m-d H:i:s',time());
            $user->end_time = date("Y-m-d H:i:s",strtotime(" +{$sendData['expire_day']} day"));
        } else {
            $date = $user->end_time;
            //已过期
            if (strtotime($date) < time()) {
                $user->start_time = date('Y-m-d H:i:s',time());
                $user->end_time = date("Y-m-d H:i:s",strtotime(" +{$sendData['expire_day']} day"));
            } else {
                $user->end_time = date("Y-m-d H:i:s",strtotime("{$date} +{$sendData['expire_day']} day"));
            }
        }

        $user->is_delete = 0;
        if (!$user->save()) {
            \Yii::error($this->getErrorMsg($user));
        }
        $this->vipUser = $user;
    }

    private function remind()
    {
        $time = strtotime($this->vipUser->end_time) - time() - 3600*24;
        $second = $time > 0 ? $time : 0;
        $id = \Yii::$app->queue->delay($second)->push(new VipCardRemindJob([
            'user' => $this->vipUser
        ]));
    }
}
