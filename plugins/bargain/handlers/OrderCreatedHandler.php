<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 16:31
 */


namespace app\plugins\bargain\handlers;


use app\events\OrderEvent;
use app\forms\common\ecard\CommonEcard;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Order;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;

class OrderCreatedHandler extends HandlerBase
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {
            /** @var OrderEvent $event */
            if ($event->order->sign != 'bargain') {
                return true;
            }

            $t = \Yii::$app->db->beginTransaction();
            try {
                $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder(\Yii::$app->mall);
                /* @var BargainOrder $bargainOrder */
                $bargainOrder = $commonBargainOrder->getTokenOrder($event->order->token);
                $commonBargainOrder->changeStatus($bargainOrder, 1);
                $bargainGoods = $bargainOrder->bargainGoods;
                $bargainGoods->success += 1;
                $bargainGoods->underway -= min($bargainGoods->underway, 1);
                $baseModel = new Model();
                if (!$bargainGoods->save()) {
                    throw new \Exception($baseModel->getErrorMsg($bargainGoods));
                }
                if ($bargainGoods->stock_type == 1) {
                    CommonEcard::getCommon()->setTypeDataOccupy($event->order);
                }
                $t->commit();
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('砍价下单成功：');
                \Yii::error($exception);
            }
        });
    }
}
