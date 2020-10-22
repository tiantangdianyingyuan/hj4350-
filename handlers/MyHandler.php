<?php

namespace app\handlers;


use app\events\UserEvent;
use app\forms\common\coupon\CommonCouponAutoSend;

class MyHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(\app\models\User::EVENT_REGISTER, function ($event) {
            // todo 事件相应处理
            try {
                /* @var UserEvent $event*/
                $form = new CommonCouponAutoSend();
                $form->event = 3;
                $form->user = $event->user;
                $form->mall = \Yii::$app->mall;
                $couponList = $form->send();
                $cacheKey = 'user_register_' . $event->user->id . '_' . \Yii::$app->mall->id;
                \Yii::$app->cache->set($cacheKey, $couponList, 100);
            } catch (\Exception $exception) {
                \Yii::error('注册事件');
            }
        });
    }
}
