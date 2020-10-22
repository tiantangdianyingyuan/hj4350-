<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/6/2
 * Time: 16:16
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\handlers;


use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Order;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;

class OrderCancelHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            /** @var OrderEvent $event */
            if ($event->order->sign != 'bargain') {
                return true;
            }

            $t = \Yii::$app->db->beginTransaction();
            try {
                $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
                /* @var BargainOrder $bargainOrder */
                $bargainOrder = $commonBargainOrder->getTokenOrder($event->order->token);
                $bargainGoods = $bargainOrder->bargainGoods;
                $bargainGoods->stock += 1;
                $baseModel = new Model();
                if (!$bargainGoods->save()) {
                    throw new \Exception($baseModel->getErrorMsg($bargainGoods));
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('砍价取消订单失败：');
                \Yii::error($exception);
            }
        });
    }
}
