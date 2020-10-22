<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 11:10
 */

namespace app\plugins\advance\handlers;

use app\handlers\HandlerBase;
use app\plugins\advance\events\GoodsEvent;
use app\plugins\advance\forms\common\CommonForm;
use app\plugins\advance\jobs\GoodsAutoOffShelvesJob;
use app\plugins\advance\models\AdvanceGoods;

class GoodsEditHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(AdvanceGoods::EVENT_EDIT, function ($event) {
            /**@var GoodsEvent $event**/

            try {
                if ($event->advanceGoods->pay_limit == -1) {
                    return ;
                }

                $oldAdvanceGoods = $event->advanceGoods->getOldAttributes();

                if (!$oldAdvanceGoods) {
                    $this->handle($event,$event->advanceGoods->pay_limit*24*3600+strtotime($event->advanceGoods->end_prepayment_at) - time());
                }

                $oldStatus = CommonForm::timeSlot($oldAdvanceGoods);
                if ($oldStatus == 3) {
                    return;
                }
                if ($oldStatus == 4) {
                    $this->handle($event,0);
                }

                $this->handle($event,$event->advanceGoods->pay_limit*24*3600+strtotime($event->advanceGoods->end_prepayment_at) - time());
            } catch (\Exception $e) {
                \Yii::error('预售商品自动下架失败');
                \Yii::error($e);
            }
        });
    }

    private function handle($event, $delay)
    {
        if ($delay < 0) {
            $delay = 0;
        }
        $class = new GoodsAutoOffShelvesJob([
            'advanceGoods' => $event->advanceGoods
        ]);
        $queueId = \Yii::$app->queue->delay($delay)->push($class);
        return;
    }
}

