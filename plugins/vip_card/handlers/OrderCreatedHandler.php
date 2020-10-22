<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/17
 * Time: 14:56
 */

namespace app\plugins\vip_card\handlers;

use app\events\OrderEvent;
use app\models\Model;
use app\models\Order;
use app\handlers\HandlerBase;
use app\plugins\vip_card\models\VipCardDiscount;
use app\plugins\vip_card\models\VipCardUser;

class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        /**@var OrderEvent $event**/
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            $t = \Yii::$app->db->beginTransaction();
            try {
                if (isset($event->pluginData) && isset($event->pluginData['vip_discount'])) {
                    $discount = $event->pluginData['vip_discount'];
                    $user = VipCardUser::findOne(['user_id' => $event->order->user_id,'mall_id' => $event->order->mall_id, 'is_delete' => 0]);
                    if (empty($user) || empty($discount) || ($discount == price_format(0))) {
                        $t->commit();
                        return ;
                    }
                    foreach ($event->order->detail as $v) {
                        $model = new VipCardDiscount();
                        $model->discount = $discount;
                        $model->discount_num = $user->image_discount;
                        $model->order_id = $event->order->id;
                        $model->order_detail_id = $v->id;
                        $model->main_id = $user->main_id;
                        $model->main_name = $user->image_main_name;
                        $model->detail_id = $user->detail_id;
                        $model->detail_name = $user->image_name;
                        if (!$model->save()) {
                            throw new \Exception((new Model())->getErrorMsg($model));
                        }
                    }
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('超级会员卡创建订单事件：');
                \Yii::error($exception);
                throw $exception;
            }
        });
    }
}
